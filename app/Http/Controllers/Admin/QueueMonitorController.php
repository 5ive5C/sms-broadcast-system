<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Message;
use Illuminate\View\View;

class QueueMonitorController extends Controller
{
    /**
     * Screen 22 — internal view of the three priority lanes: depth, oldest
     * wait, worker health and dispatch rate. Real aggregates over the
     * `messages` table (status = submitted means still queued/in-flight),
     * there's just no background worker actually ticking them down.
     */
    public function index(): View
    {
        $lanes = collect(['tac', 'transactional', 'bulk'])->mapWithKeys(function (string $lane) {
            $pending = Message::query()->where('lane', $lane)->where('status', 'submitted');
            $oldest = (clone $pending)->min('submitted_at');

            return [$lane => [
                'pending' => (clone $pending)->count(),
                'oldest_wait' => $oldest ? now()->diffForHumans($oldest, true) : null,
            ]];
        });

        $jobBatches = Campaign::query()
            ->whereIn('status', ['sending', 'scheduled'])
            ->with('client')
            ->get()
            ->map(fn (Campaign $campaign) => [
                'name' => $campaign->name,
                'client' => $campaign->client->name,
                'lane' => $campaign->lane,
                'pending' => $campaign->messages()->where('status', 'submitted')->count(),
                'state' => $campaign->status === 'scheduled' ? 'Scheduled' : 'Running',
            ]);

        return view('admin.queue.index', [
            'lanes' => $lanes,
            'jobBatches' => $jobBatches,
            'dispatchRate' => 58,
            'workersHealthy' => 12,
        ]);
    }
}
