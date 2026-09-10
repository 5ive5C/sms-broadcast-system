# SMS Broadcast System — Masterplan

## 1. Overview

A standalone, multi-tenant **SMS broadcasting middleware** that sits between client
systems (and their staff, via a portal) and the **iSMS gateway**. iSMS owns the actual
telco relationship (carrier routing, delivery); this system's job is to accept send
requests (API or portal), queue and dispatch them to iSMS, track status, meter usage
against prepaid credit, and report on all of it.

The first client is a **bank**, using the system primarily for **TAC/OTP** codes and
**transactional alerts**. The platform is built multi-tenant from day one so additional
clients can be onboarded later without rearchitecting.

This is a **new, standalone service** — not part of any existing billing/customer
systems.

## 2. Goals

- Give client systems a simple, reliable API to send TAC/OTP and transactional SMS in
  near real time, without needing to integrate with iSMS directly.
- Give client staff a portal to run bulk SMS campaigns, monitor delivery, manage their
  own users/API keys, and track credit usage.
- Guarantee TAC/OTP messages are never delayed behind bulk campaign traffic.
- Track delivery status accurately even though the telco's final confirmation can take
  up to a few days to arrive from iSMS.
- Run a prepaid, tiered-pricing credit system per client, simple enough to launch with
  a manual top-up process and evolve into automated payments later.
- Keep the system multi-tenant and clean enough to onboard new clients beyond the bank
  without special-casing.

## 3. Target Users

| User type | Description |
|---|---|
| **Client systems (machine)** | Bank's backend calling the API to send TAC/OTP and transactional alerts in real time. |
| **Client staff — Admin** | Manages their organization's API keys, sender ID, sub-users/roles, runs/monitors bulk campaigns, views usage & balance. |
| **Client staff — Standard user** | Limited-permission staff (e.g., can run campaigns but not manage API keys or top-ups), depending on role assigned by the client admin. |
| **Internal (your) super-admin** | Onboards clients, approves manual top-ups, has visibility across all clients, runs reconciliation against iSMS reports. |

## 4. Core Features

### 4.1 Message Sending
- **API — real-time send**: single-message send for TAC/OTP and transactional alerts.
  Free-text message body; a `type` parameter (`tac` / `transactional` / `bulk`) tells
  the queue how to prioritize it.
- **API — bulk send**: client systems can also submit bulk sends programmatically, not
  just through the portal.
- **Portal — bulk send**: staff upload a recipient list (e.g., CSV) and message content
  to launch a campaign (up to ~100k recipients per campaign for now).
- Every client has a **configurable Sender ID**.

### 4.2 Queueing & Delivery
- **Priority queueing**: TAC and transactional messages are dispatched ahead of bulk
  campaign traffic, so a large campaign in flight never delays an OTP.
- **Throttling to iSMS**: outbound dispatch rate is configurable/tunable so the system
  respects whatever request-rate limits iSMS enforces (exact limits to be confirmed
  with iSMS; design for "configurable now, tighten later").
- **Retry policy**: on failure to submit to iSMS (network error, iSMS rejection), retry
  up to **3 attempts with backoff**; after the 3rd failure, mark the message **Failed**
  and stop — no silent infinite retries.
- **Manual resend**: failed messages are visible in the portal, with a resend action.

### 4.3 Delivery Status Tracking
Two-stage status lifecycle, since iSMS itself gets final delivery confirmation from the
telco asynchronously (sometimes up to ~3 days later):

1. **Submitted** — iSMS accepts the message immediately at send time (fast ack).
2. **Delivered / Failed (final)** — obtained later by a **background poller** that
   periodically checks iSMS for the true telco delivery outcome and updates the
   message record.

Clients only ever see status **as tracked on our end** (Submitted → Delivered/Failed).
They do not get direct access to raw iSMS/telco data.

### 4.4 Billing & Credits
- **Prepaid wallet per client.** Every send debits credit from the client's balance.
- **Tiered pricing**: cost per credit depends on top-up size (e.g., 1,000 credits @
  RM0.12/credit, 10,000 credits @ RM0.11/credit — exact tiers configurable by internal
  admin, not hardcoded).
- **Manual top-up (Phase 1)**: client arranges payment off-platform (bank
  transfer/invoice); internal admin credits the wallet in the portal once payment is
  confirmed. No payment gateway integration needed yet.
- **Automated top-up (Phase 2, later)**: online payment gateway integration so clients
  can top up directly.
- Balance is visible to clients via both **API** (balance-check endpoint) and **portal**.

### 4.5 Portal & Access Control
- **Internal super-admin**: onboard/manage clients, configure pricing tiers, approve
  manual top-ups, view all clients' data, run **iSMS reconciliation reports**
  (admin-only — comparing internal delivery records against iSMS's own reports for
  accuracy/dispute purposes).
- **Client admin**: manage their own API key(s), sender ID, staff accounts and roles,
  launch/monitor campaigns, view usage/balance reports.
- **Client staff**: scoped permissions assigned by their client admin (e.g., can launch
  campaigns but can't manage billing or API keys) — a simple per-client role/permission
  model, not a fixed two-role system.

### 4.6 Reporting
- **Client-facing**: daily/monthly usage summaries, cost breakdown, credit balance
  history/top-up log, campaign-level delivery stats (submitted/delivered/failed).
- **Admin-only**: cross-client visibility, reconciliation reports against iSMS's own
  delivery reports.

## 5. System Architecture (Conceptual)

```
                     ┌──────────────────────┐
   Client systems ──▶│                      │
   (real-time API)   │                      │       ┌───────────────┐
                      │   SMS Broadcast      │──────▶│  iSMS Gateway │──▶ Telco
   Client portal ────▶│   Middleware         │       └───────────────┘
   (bulk, mgmt)       │   (Laravel + MySQL)  │              │
                      │                      │◀─────────────┘
                      │  - Priority queue     │   (status polling)
                      │  - Credit wallet      │
                      │  - Status tracking    │
                      │  - Reporting          │
                      └──────────────────────┘
```

Key building blocks:

- **API layer** — authenticates client requests, validates/normalizes the message,
  checks wallet balance, enqueues the job.
- **Queue layer** — separate priority lanes (e.g., `tac`, `transactional`, `bulk`)
  processed by workers, with TAC/transactional lanes drained first. Sized to comfortably
  handle hundreds of thousands of TAC messages/day and campaigns up to ~100k recipients,
  with headroom for spikes on both our side and iSMS's side (throttled dispatch).
- **Dispatcher/worker** — calls iSMS's send API, records the submit-time result, debits
  the wallet, applies the retry policy on failure.
- **Status poller** — a scheduled background job that queries iSMS for final delivery
  status on outstanding "Submitted" messages and updates records accordingly.
- **Wallet/billing module** — tiered pricing, atomic credit debits (must be safe under
  concurrent high-volume sending — no overselling credit), manual top-up approval
  workflow.
- **Portal (web UI)** — campaign management, reporting, user/role management, top-up
  requests, admin reconciliation views.
- **Reconciliation job** — admin-only, periodically compares internal records against
  iSMS's own reports to catch discrepancies.

## 6. Core Data Entities (conceptual, not schema)

- **Client** — tenant record: name, status, sender ID(s), pricing tier.
- **ApiKey** — per-client key/secret, optional IP whitelist, active/revoked state.
- **PortalUser** — belongs to a client (or is an internal admin), has a role.
- **Role/Permission** — per-client configurable roles for portal staff.
- **Wallet** — one per client: current credit balance.
- **PricingTier** — top-up size → price-per-credit mapping.
- **TopUpRequest** — amount, status (pending/approved), approved-by, timestamp.
- **Message** — single SMS record: client, type (tac/transactional/bulk), recipient,
  content, status (queued/submitted/delivered/failed), attempt count, campaign
  reference (if bulk).
- **Campaign** — groups bulk messages: name, recipient count, created-by, status summary.
- **StatusPollLog / ReconciliationRecord** — internal tracking for the async status
  poller and admin reconciliation.

## 7. Tech Stack

- **Backend**: Laravel (PHP), MySQL
- **Queue**: Laravel Queues — recommend **Redis** as the queue driver at this volume
  (hundreds of thousands of messages/day) over the database driver, with **Laravel
  Horizon** for queue monitoring/visibility and priority-lane configuration.
- **Scheduling**: Laravel's task scheduler (cron-driven) for the iSMS status poller and
  reconciliation jobs.
- **Auth — API**: API key + secret per client over HTTPS (e.g., Laravel Sanctum-style
  token auth), optional per-client IP whitelist.
- **Auth — Portal**: standard hashed-password login (Laravel's built-in auth), with
  **2FA** for admin-level portal users (client admins and internal admins), via a
  package like Laravel Fortify.
- **Hosting**: cloud (no hard constraint yet — a Malaysia-region cloud provider is a
  reasonable default given the banking client, but open to change).

## 8. Security & Compliance Notes

- HTTPS enforced everywhere; no exceptions.
- API keys are per-client, rotatable, and revocable; secrets hashed at rest.
- Optional IP whitelisting per client (useful for the bank, not mandatory for every
  future client type).
- 2FA required for admin-tier portal accounts.
- Avoid retaining plaintext OTP/TAC content longer than operationally necessary — log
  that a TAC was sent and its outcome, not an indefinitely-retained plaintext code.
- Wallet debits must be atomic/concurrency-safe (row-level locking or equivalent) to
  prevent race conditions from overspending credit under high parallel send volume.

## 9. Known Technical Challenges to Design For

- **Priority under load** — ensuring a 100k-recipient campaign never starves the TAC
  lane; needs real queue prioritization, not just "process in order."
- **Throttling to iSMS** — iSMS's own rate limits aren't fully known yet; the dispatch
  layer should be built with a configurable rate limiter so it can be tuned without a
  redesign once real limits are confirmed.
- **Queue durability** — sends and their retry state must survive worker restarts/
  deploys (Redis persistence + Horizon supervisor config).
- **Delayed final status** — the data model must cleanly represent "submitted but not
  yet finally confirmed" for up to several days, without that being mistaken for
  failure.
- **Multi-tenant data isolation** — every query and job must be scoped to the correct
  client; with a handful of large B2B clients (not thousands of small tenants), a
  shared-database, `client_id`-scoped design is simpler to build and operate than
  per-tenant databases, while still keeping data cleanly isolated.

## 10. Phased Roadmap

**Phase 1 — MVP**
- API: TAC/transactional real-time send, bulk send
- Portal: bulk campaign upload, client/user/role management, sender ID config
- Priority queue with throttled iSMS dispatch, 3-attempt retry, manual resend
- Status poller for delayed final delivery status
- Prepaid wallet, tiered pricing, **manual** top-up + approval workflow
- Balance check (API + portal)
- Client-facing reporting (usage, cost, balance history)
- Admin reconciliation vs. iSMS reports

**Phase 2 — Automation**
- Automated online top-up via payment gateway
- Refinements to throttling once real iSMS rate limits are confirmed in production

**Phase 3 — Growth**
- Onboarding additional clients beyond the bank
- Deeper analytics/reporting as usage patterns emerge

## 11. Open Items to Confirm Later

- Exact iSMS rate limits / API contract details (once available from iSMS)
- Final list of client-side roles/permissions beyond admin vs. standard staff
- Data retention policy specifics for message content and TAC codes
- Target cloud provider/region
