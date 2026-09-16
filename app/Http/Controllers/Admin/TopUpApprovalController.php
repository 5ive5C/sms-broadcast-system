<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TopUpRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopUpApprovalController extends Controller
{
    /**
     * Screen 10 — internal queue of payment confirmations.
     */
    public function index(): View
    {
        $requests = TopUpRequest::query()
            ->with(['client', 'approver'])
            ->orderByRaw("status = 'pending' desc")
            ->orderByDesc('created_at')
            ->get();

        return view('admin.top-ups.index', [
            'requests' => $requests,
            'pendingCount' => $requests->where('status', 'pending')->count(),
            'approvedCount' => $requests->where('status', 'approved')->count(),
        ]);
    }

    /**
     * Approving credits the wallet and stamps who did it.
     */
    public function approve(Request $request, TopUpRequest $topUpRequest): RedirectResponse
    {
        abort_unless($topUpRequest->isPending(), 409);

        $topUpRequest->approve($request->user());

        return redirect()->route('top-ups.index')
            ->with('status', $topUpRequest->reference().' approved — wallet credited.');
    }

    public function reject(Request $request, TopUpRequest $topUpRequest): RedirectResponse
    {
        abort_unless($topUpRequest->isPending(), 409);

        $topUpRequest->reject($request->user(), $request->input('notes'));

        return redirect()->route('top-ups.index')
            ->with('status', $topUpRequest->reference().' rejected.');
    }
}
