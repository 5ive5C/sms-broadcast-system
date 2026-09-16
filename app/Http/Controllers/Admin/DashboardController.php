<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Message;
use App\Models\ReconciliationRun;
use App\Models\TopUpRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * The internal admin's own landing page — platform-wide, not scoped to
     * any one tenant (that's Client Dashboard, reached via "View as").
     */
    public function index(): View
    {
        $latestRun = ReconciliationRun::query()->latest('run_date')->first();

        return view('admin.dashboard.index', [
            'clientCounts' => Client::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'pendingTopUps' => TopUpRequest::query()->pending()->count(),
            'sentToday' => Message::query()->whereDate('submitted_at', today())->count(),
            'failedToday' => Message::query()->whereDate('submitted_at', today())->where('status', 'failed')->count(),
            'reconciliationMatch' => $latestRun?->matched_pct,
            'recentAudit' => AuditLog::query()->latest()->limit(8)->get(),
        ]);
    }
}
