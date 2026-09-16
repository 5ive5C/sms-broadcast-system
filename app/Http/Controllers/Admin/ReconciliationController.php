<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Message;
use App\Models\ReconciliationRecord;
use App\Models\ReconciliationRun;
use App\Models\WalletLedgerEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReconciliationController extends Controller
{
    /**
     * Screen 28 — daily comparison of our delivery records against the
     * iSMS report. Internal only; clients see the outcome in their ledger,
     * not this screen.
     */
    public function index(): View
    {
        $run = ReconciliationRun::query()->latest('run_date')->with('records.client', 'records.message')->first();

        return view('admin.reconciliation.index', ['run' => $run]);
    }

    /**
     * Compares our recorded status against each message's simulated iSMS
     * status (seeded to disagree on a small slice) and produces a fresh
     * run. Delivered-vs-failed disagreements are resolved automatically by
     * refunding the credit — everything else is flagged for review.
     */
    public function rerun(Request $request): RedirectResponse
    {
        $run = DB::transaction(function () use ($request) {
            $mismatches = Message::query()
                ->whereNotNull('isms_status')
                ->whereColumn('isms_status', '!=', 'status')
                ->where('submitted_at', '>=', now()->subDays(30))
                ->get();

            $ourRecords = Message::query()->where('submitted_at', '>=', now()->subDays(30))->count();

            $run = ReconciliationRun::create([
                'run_date' => now()->toDateString(),
                'our_records' => $ourRecords,
                'isms_report' => $ourRecords - $mismatches->count() + $mismatches->where('isms_status', 'no_record')->count(),
                'matched_pct' => $ourRecords > 0 ? round((($ourRecords - $mismatches->count()) / $ourRecords) * 100, 2) : 100,
                'discrepancy_count' => $mismatches->count(),
            ]);

            foreach ($mismatches as $message) {
                $resolution = 'Under review';

                if ($message->status === 'delivered' && $message->isms_status === 'failed') {
                    $wallet = $message->client->wallet()->lockForUpdate()->first();

                    if ($wallet && $message->credit_refunded === 0) {
                        WalletLedgerEntry::post($wallet, 'refund', 'Refund — reconciliation mismatch '.$message->code, (float) $message->parts);
                        $message->update(['status' => 'failed', 'credit_refunded' => $message->parts, 'final_at' => now()]);
                    }

                    $resolution = 'Credit refunded';
                } elseif ($message->isms_status === 'no_record') {
                    $resolution = 'Raise with iSMS';
                }

                ReconciliationRecord::create([
                    'reconciliation_run_id' => $run->id,
                    'message_id' => $message->id,
                    'client_id' => $message->client_id,
                    'our_status' => $message->status,
                    'isms_status' => $message->isms_status,
                    'resolution' => $resolution,
                ]);
            }

            return $run;
        });

        AuditLog::record($request->user(), 'Reconciliation re-run', $run->discrepancy_count.' discrepancies found');

        return redirect()->route('reconciliation.index')->with('status', 'Reconciliation re-run complete.');
    }
}
