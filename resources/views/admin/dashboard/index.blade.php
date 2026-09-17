@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>SMS Broadcast</h1>
    <div class="text-body-secondary small">Platform overview &middot; {{ now()->format('d M Y') }}</div>
@stop

@section('content')

    @include('partials.sweetalert')

    <div class="row mb-4">
        <div class="col-md-3">
            <x-adminlte-info-box title="Active clients" text="{{ number_format($clientCounts['active'] ?? 0) }}" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Pending top-ups" text="{{ number_format($pendingTopUps) }}" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Sent today" text="{{ number_format($sentToday) }}" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Reconciliation match" text="{{ $reconciliationMatch !== null ? $reconciliationMatch.'%' : '—' }}" />
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <x-adminlte-card>
                <x-slot name="titleSlot">Clients by Status</x-slot>

                <ul class="list-unstyled mb-0">
                    @foreach(['active' => 'Active', 'pending' => 'Pending', 'suspended' => 'Suspended', 'inactive' => 'Inactive'] as $status => $label)
                        <li class="d-flex justify-content-between border-bottom py-1">
                            <span>{{ $label }}</span>
                            <span class="fw-bold">{{ number_format($clientCounts[$status] ?? 0) }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-adminlte-card>
        </div>

        <div class="col-md-8 mb-4">
            <x-adminlte-card>
                <x-slot name="titleSlot">Recent Activity</x-slot>

                <table class="table table-sm mb-0">
                    <tbody>
                        @forelse($recentAudit as $log)
                            <tr>
                                <td class="text-body-secondary" style="width:130px;">{{ $log->created_at->format('d M H:i') }}</td>
                                <td class="fw-semibold">{{ $log->action }}</td>
                                <td>{{ $log->actor_name }}</td>
                                <td class="text-body-secondary">{{ $log->detail }}</td>
                            </tr>
                        @empty
                            <tr><td class="text-body-secondary text-center py-3">No activity yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </x-adminlte-card>
        </div>
    </div>

@stop
