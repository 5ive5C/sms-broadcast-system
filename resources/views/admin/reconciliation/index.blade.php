@extends('adminlte::page')

@section('title', 'iSMS Reconciliation')

@section('content_header')
    <h1>Reconciliation @if($run) — {{ $run->run_date->format('d M Y') }} @endif</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <div class="d-flex justify-content-end mb-3">
        <form action="{{ route('reconciliation.rerun') }}" method="post">
            @csrf
            <button type="submit" class="btn btn-primary">Re-run</button>
        </form>
    </div>

    @if($run)
        <div class="row text-center mb-4">
            <div class="col">
                <div class="fs-4 fw-bold">{{ number_format($run->our_records) }}</div>
                <div class="small text-body-secondary">Our records</div>
            </div>
            <div class="col">
                <div class="fs-4 fw-bold">{{ number_format($run->isms_report) }}</div>
                <div class="small text-body-secondary">iSMS report</div>
            </div>
            <div class="col">
                <div class="fs-4 fw-bold text-success">{{ $run->matched_pct }}%</div>
                <div class="small text-body-secondary">Matched</div>
            </div>
            <div class="col">
                <div class="fs-4 fw-bold text-danger">{{ number_format($run->discrepancy_count) }}</div>
                <div class="small text-body-secondary">Discrepancies</div>
            </div>
        </div>

        <x-adminlte-card>
            <x-slot name="titleSlot">Discrepancies</x-slot>

            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Message ID</th>
                        <th>Client</th>
                        <th>Our status</th>
                        <th>iSMS status</th>
                        <th>Resolution</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($run->records as $record)
                        <tr>
                            <td>{{ $record->message?->code ?? '—' }}</td>
                            <td>{{ $record->client->name }}</td>
                            <td>{{ ucfirst($record->our_status) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $record->isms_status)) }}</td>
                            <td>{{ $record->resolution }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-body-secondary py-4">No discrepancies.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-adminlte-card>
    @else
        <p class="text-body-secondary">No reconciliation run yet — click Re-run to produce one.</p>
    @endif

    <p class="small text-body-secondary mt-3">Runs nightly. Clients see the outcome in their ledger, not this screen.</p>

@stop
