@extends('adminlte::page')

@section('title', 'Usage and Cost Report')

@section('content_header')
    <h1>Usage and Cost — {{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <x-adminlte-card>
        <x-slot name="titleSlot">Usage and Cost</x-slot>

        <form method="get" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label mb-0 small">Month</label>
                <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>
        </form>

        @php($labels = ['tac' => 'TAC / OTP', 'transactional' => 'Transactional', 'bulk' => 'Bulk campaigns'])

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Messages</th>
                    <th>Credits</th>
                    <th>Delivered</th>
                    <th>Cost</th>
                </tr>
            </thead>
            <tbody>
                @php($totalMessages = 0)
                @php($totalCredits = 0)
                @php($totalDelivered = 0)
                @foreach($labels as $lane => $label)
                    @php($row = $rows->get($lane))
                    @php($totalMessages += $row->messages ?? 0)
                    @php($totalCredits += $row->credits ?? 0)
                    @php($totalDelivered += $row->delivered ?? 0)
                    <tr>
                        <td class="fw-semibold">{{ $label }}</td>
                        <td>{{ number_format($row->messages ?? 0) }}</td>
                        <td>{{ number_format($row->credits ?? 0) }}</td>
                        <td>{{ ($row->messages ?? 0) > 0 ? round((($row->delivered ?? 0) / $row->messages) * 100, 1) : 0 }}%</td>
                        <td>RM{{ number_format(($row->credits ?? 0) * $pricePerCredit, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="fw-bold">
                    <td>Total</td>
                    <td>{{ number_format($totalMessages) }}</td>
                    <td>{{ number_format($totalCredits) }}</td>
                    <td>{{ $totalMessages > 0 ? round(($totalDelivered / $totalMessages) * 100, 1) : 0 }}%</td>
                    <td>RM{{ number_format($totalCredits * $pricePerCredit, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </x-adminlte-card>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <x-adminlte-card>
                <x-slot name="titleSlot">By Campaign</x-slot>

                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($byCampaign as $row)
                            <tr>
                                <td>{{ $row->campaign?->name ?? 'Deleted campaign' }}</td>
                                <td class="text-end">RM{{ number_format($row->credits * $pricePerCredit, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-body-secondary text-center py-3">No campaign spend this month.</td></tr>
                        @endforelse
                        <tr>
                            <td>API traffic (no campaign)</td>
                            <td class="text-end">RM{{ number_format($apiCredits * $pricePerCredit, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </x-adminlte-card>
        </div>
    </div>

@stop
