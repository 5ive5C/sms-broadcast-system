<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, \Closure $next) {
            Gate::authorize('reports.view');

            return $next($request);
        });
    }

    /**
     * Screen 24 — every recipient and the status of their message.
     */
    public function delivery(Request $request): View
    {
        $query = $this->filtered($request);

        $totals = (clone $query)->selectRaw("
                count(*) as matching,
                sum(status = 'delivered') as delivered,
                sum(status = 'submitted') as submitted,
                sum(status = 'failed') as failed,
                sum(credit_charged - credit_refunded) as credits
            ")->first();

        return view('reports.delivery', [
            'messages' => (clone $query)->latest('submitted_at')->paginate(25)->withQueryString(),
            'totals' => $totals,
            'campaigns' => $request->user()->actingClient()->campaigns()->orderByDesc('created_at')->limit(50)->get(),
        ]);
    }

    public function deliveryExport(Request $request): StreamedResponse
    {
        $messages = $this->filtered($request)->orderByDesc('submitted_at')->get();

        return response()->streamDownload(function () use ($messages) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Recipient', 'Campaign', 'Type', 'Status', 'Submitted', 'Final At', 'Parts', 'Credits']);

            foreach ($messages as $message) {
                fputcsv($out, [
                    $message->recipient,
                    $message->campaign?->name ?? '',
                    $message->lane,
                    ucfirst($message->status),
                    $message->submitted_at->format('d M Y H:i'),
                    $message->final_at?->format('d M Y H:i') ?? 'awaiting telco',
                    $message->parts,
                    $message->credit_charged - $message->credit_refunded,
                ]);
            }

            fclose($out);
        }, 'delivery-report.csv');
    }

    /**
     * Screen 27 — monthly usage and spend by message type.
     */
    public function usage(Request $request): View
    {
        $client = $request->user()->actingClient();
        $month = $request->input('month', now()->format('Y-m'));

        $rows = Message::query()
            ->where('client_id', $client->id)
            ->whereRaw("date_format(submitted_at, '%Y-%m') = ?", [$month])
            ->selectRaw("
                lane,
                count(*) as messages,
                sum(credit_charged - credit_refunded) as credits,
                sum(status = 'delivered') as delivered
            ")
            ->groupBy('lane')
            ->get()
            ->keyBy('lane');

        $priceByLane = ['tac' => 0.095, 'transactional' => 0.095, 'bulk' => 0.095];
        $pricePerCredit = (float) ($client->pricingTier?->price_per_credit ?? 0.12);

        $byCampaign = Message::query()
            ->where('client_id', $client->id)
            ->whereNotNull('campaign_id')
            ->whereRaw("date_format(submitted_at, '%Y-%m') = ?", [$month])
            ->selectRaw('campaign_id, sum(credit_charged - credit_refunded) as credits')
            ->groupBy('campaign_id')
            ->with('campaign:id,name')
            ->get();

        $apiCredits = Message::query()
            ->where('client_id', $client->id)
            ->whereNull('campaign_id')
            ->whereRaw("date_format(submitted_at, '%Y-%m') = ?", [$month])
            ->sum(\Illuminate\Support\Facades\DB::raw('credit_charged - credit_refunded'));

        return view('reports.usage', [
            'month' => $month,
            'rows' => $rows,
            'pricePerCredit' => $pricePerCredit,
            'byCampaign' => $byCampaign,
            'apiCredits' => $apiCredits,
        ]);
    }

    protected function filtered(Request $request)
    {
        $client = $request->user()->actingClient();

        return Message::query()
            ->where('client_id', $client->id)
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('submitted_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('submitted_at', '<=', $request->date_to))
            ->when($request->filled('campaign_id'), fn ($q) => $q->where('campaign_id', $request->campaign_id))
            ->when($request->filled('status') && $request->status !== 'all', fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('recipient'), fn ($q) => $q->where('recipient', 'like', '%'.$request->recipient.'%'))
            ->with('campaign');
    }
}
