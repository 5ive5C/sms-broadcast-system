<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiKeyRequest;
use App\Models\ApiKey;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, \Closure $next) {
            Gate::authorize('api-keys.manage');

            return $next($request);
        });
    }

    /**
     * Screen 05 — every API key issued to the client, active or revoked.
     */
    public function index(Request $request): View
    {
        $apiKeys = ApiKey::query()
            ->where('client_id', $request->user()->actingClient()->id)
            ->orderByRaw('revoked_at is not null')
            ->orderByDesc('created_at')
            ->get();

        return view('api-keys.index', [
            'apiKeys' => $apiKeys,
            'plainSecret' => session('api_key_plain_secret'),
        ]);
    }

    /**
     * Generate a new key/secret pair. The plain secret is flashed once
     * through the session — the same "displayed once, store it now" flow
     * the screen calls out.
     */
    public function store(StoreApiKeyRequest $request): RedirectResponse
    {
        $actor = $request->user();

        [$apiKey, $plainSecret] = ApiKey::generate($actor->actingClient(), $request->validated('name'));

        if ($whitelist = $request->validated('ip_whitelist')) {
            $ips = preg_split('/[\r\n,]+/', $whitelist, -1, PREG_SPLIT_NO_EMPTY);
            $apiKey->forceFill(['ip_whitelist' => array_map('trim', $ips)])->save();
        }

        AuditLog::record($actor, 'API key generated', $apiKey->key.'••••');

        return redirect()->route('api-keys.index')
            ->with('status', 'API key generated.')
            ->with('api_key_plain_secret', 'sk_live_'.$plainSecret);
    }

    /**
     * Revoke a key. Revoked keys stay listed (audit trail) but can no
     * longer authenticate.
     */
    public function revoke(Request $request, ApiKey $apiKey): RedirectResponse
    {
        if ($apiKey->client_id !== $request->user()->actingClient()?->id) {
            abort(403);
        }

        $apiKey->revoke();

        AuditLog::record($request->user(), 'API key revoked', $apiKey->key.'••••');

        return redirect()->route('api-keys.index')->with('status', 'API key revoked.');
    }
}
