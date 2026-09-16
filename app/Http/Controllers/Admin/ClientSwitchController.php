<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;

class ClientSwitchController extends Controller
{
    /**
     * Lets the internal super-admin step into a client's portal to see —
     * or debug — exactly what that tenant sees. Session-only, never
     * persisted, and never changes who the audit log attributes actions to
     * (AuditLog still records the real actor).
     */
    public function viewAs(Client $client): RedirectResponse
    {
        session(['viewing_client_id' => $client->id]);

        return redirect()->route('dashboard')->with('status', 'Viewing as '.$client->name.'.');
    }

    public function exit(): RedirectResponse
    {
        session()->forget('viewing_client_id');

        return redirect()->route('clients.index')->with('status', 'Exited client view.');
    }
}
