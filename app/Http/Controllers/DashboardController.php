<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Screen 13 — client landing page. Volume, delivery rate, balance and
     * alerts for one tenant only.
     */
    public function index(Request $request): View
    {
        $client = $request->user()->actingClient();
        $wallet = $client->wallet;

        $todayMessages = Message::query()->where('client_id', $client->id)->whereDate('submitted_at', today());

        $sentToday = (clone $todayMessages)->count();
        $deliveredToday = (clone $todayMessages)->where('status', 'delivered')->count();
        $awaiting = (clone $todayMessages)->where('status', 'submitted')->count();
        $deliveryRate = $sentToday > 0 ? round(($deliveredToday / $sentToday) * 100, 1) : 0;

        $volumeByHour = (clone $todayMessages)
            ->select(DB::raw('HOUR(submitted_at) as hour'), DB::raw('count(*) as total'))
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $failedLast7Days = Message::query()
            ->where('client_id', $client->id)
            ->where('status', 'failed')
            ->where('submitted_at', '>=', now()->subDays(7))
            ->count();

        $sendingCampaign = $client->campaigns()->where('status', 'sending')->first();

        $usedThisMonth = abs((float) $wallet->ledgerEntries()
            ->whereIn('type', ['campaign', 'quick_send', 'api'])
            ->whereMonth('created_at', now()->month)
            ->sum('change'));
        $runwayDays = $usedThisMonth > 0 ? max(1, (int) round(($wallet->balance / $usedThisMonth) * 30)) : null;

        return view('dashboard.index', [
            'client' => $client,
            'wallet' => $wallet,
            'sentToday' => $sentToday,
            'deliveryRate' => $deliveryRate,
            'awaiting' => $awaiting,
            'volumeByHour' => $volumeByHour,
            'failedLast7Days' => $failedLast7Days,
            'sendingCampaign' => $sendingCampaign,
            'runwayDays' => $runwayDays,
        ]);
    }
}
