@extends('adminlte::page')

@section('title', 'Delivery Report')

@section('content_header')
    <h1>Delivery Report</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <x-adminlte-card>
        <x-slot name="titleSlot">Delivery Report</x-slot>

        <form method="get" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label mb-0 small">Date from</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <label class="form-label mb-0 small">Date to</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <label class="form-label mb-0 small">Campaign</label>
                <select name="campaign_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected((int) request('campaign_id') === $campaign->id)>{{ $campaign->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-0 small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    @foreach(['all', 'delivered', 'submitted', 'failed'] as $option)
                        <option value="{{ $option }}" @selected(request('status', 'all') === $option)>{{ $option === 'all' ? 'All' : ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-0 small">Recipient</label>
                <input type="text" name="recipient" value="{{ request('recipient') }}" class="form-control form-control-sm" placeholder="Search…">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('reports.delivery.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">Export CSV</a>
            </div>
        </form>

        <div class="row text-center mb-4">
            <div class="col">
                <div class="fs-4 fw-bold">{{ number_format($totals->matching) }}</div>
                <div class="small text-body-secondary">matching</div>
            </div>
            <div class="col">
                <div class="fs-4 fw-bold text-success">{{ number_format($totals->delivered) }}</div>
                <div class="small text-body-secondary">delivered</div>
            </div>
            <div class="col">
                <div class="fs-4 fw-bold text-body-secondary">{{ number_format($totals->submitted) }}</div>
                <div class="small text-body-secondary">submitted</div>
            </div>
            <div class="col">
                <div class="fs-4 fw-bold text-danger">{{ number_format($totals->failed) }}</div>
                <div class="small text-body-secondary">failed</div>
            </div>
            <div class="col">
                <div class="fs-4 fw-bold">{{ number_format($totals->credits) }}</div>
                <div class="small text-body-secondary">credits used</div>
            </div>
        </div>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Campaign</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Final at</th>
                    <th>Parts</th>
                    <th>Credits</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr>
                        <td>{{ $message->recipient }}</td>
                        <td>{{ $message->campaign?->name ?? '—' }}</td>
                        <td>{{ $message->lane }}</td>
                        <td>
                            @php($theme = ['delivered' => 'green', 'submitted' => 'slate', 'failed' => 'red'][$message->status])
                            <span class="pill pill-{{ $theme }}">{{ ucfirst($message->status) }}</span>
                        </td>
                        <td>{{ $message->submitted_at->format('d M H:i') }}</td>
                        <td>{{ $message->final_at?->format('d M H:i') ?? 'awaiting telco' }}</td>
                        <td>{{ $message->parts }}</td>
                        <td>{{ $message->credit_charged - $message->credit_refunded }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-body-secondary py-4">No recipients match.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $messages->links() }}

        <p class="small text-body-secondary mt-2 mb-0">Failed messages rejected by iSMS are refunded and show 0 credits.</p>
    </x-adminlte-card>

@stop
