@extends('adminlte::page')

@section('title', 'Queue Monitor')

@section('content_header')
    <h1>Queue Monitor</h1>
    <div class="text-body-secondary small">{{ $workersHealthy }} workers healthy &middot; dispatch {{ $dispatchRate }} msg/s</div>
@stop

@section('content')

    <div class="row mb-4">
        @foreach($lanes as $lane => $stats)
            <div class="col-md-4">
                <x-adminlte-card>
                    <x-slot name="titleSlot">Lane &middot; {{ $lane }}</x-slot>
                    <div class="fs-3 fw-bold">{{ number_format($stats['pending']) }}</div>
                    <div class="small text-body-secondary">oldest wait {{ $stats['oldest_wait'] ?? '—' }}</div>
                </x-adminlte-card>
            </div>
        @endforeach
    </div>

    <x-adminlte-card icon="bi bi-list-task">
        <x-slot name="titleSlot">Job Batches</x-slot>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Job batch</th>
                    <th>Client</th>
                    <th>Lane</th>
                    <th>Pending</th>
                    <th>State</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobBatches as $batch)
                    <tr>
                        <td class="fw-semibold">{{ $batch['name'] }}</td>
                        <td>{{ $batch['client'] }}</td>
                        <td>{{ $batch['lane'] }}</td>
                        <td>{{ number_format($batch['pending']) }}</td>
                        <td>
                            <span class="badge text-bg-{{ $batch['state'] === 'Running' ? 'success' : 'info' }}">{{ $batch['state'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-body-secondary py-4">No active job batches.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-adminlte-card>

    <x-adminlte-card icon="bi bi-speedometer2">
        <x-slot name="titleSlot">Dispatch Rate Limit</x-slot>

        <div class="d-flex align-items-center gap-3">
            <span class="fs-4 fw-bold">{{ $dispatchRate }} msg/s</span>
            <span class="small text-body-secondary">Global cap protecting the TAC lane from bulk sends.</span>
        </div>
    </x-adminlte-card>

@stop
