<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Screen 29 — who did what, and when. Immutable; internal admins see
     * every client's entries here (client admins see only their own,
     * surfaced elsewhere, not on this internal-only screen).
     */
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('client')
            ->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->client_id))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'clients' => Client::query()->orderBy('name')->get(),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
