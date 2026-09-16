@extends('adminlte::page')

@section('title', 'Wallet')

@section('content_header')
    <h1>Wallet</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <div class="row mb-4">
        <div class="col-md-3">
            <x-adminlte-info-box title="Available credit" text="{{ number_format($wallet->balance) }}" icon="bi bi-wallet2" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Used this month" text="{{ number_format($usedThisMonth) }}" icon="bi bi-graph-down" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Refunded" text="{{ number_format($refunded) }}" icon="bi bi-arrow-counterclockwise" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Runway" text="~{{ $usedThisMonth > 0 ? max(1, round(($wallet->balance / $usedThisMonth) * 30)) : '—' }} days" icon="bi bi-hourglass-split" />
        </div>
    </div>

    <x-adminlte-card icon="bi bi-list-ul">
        <x-slot name="titleSlot">Ledger</x-slot>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entry</th>
                    <th>Change</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>{{ $entry->created_at->format('d M') }}</td>
                        <td>{{ $entry->description }}</td>
                        <td class="{{ $entry->change >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                            {{ $entry->change >= 0 ? '+' : '' }}{{ number_format($entry->change) }}
                        </td>
                        <td>{{ number_format($entry->balance_after) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-body-secondary py-4">No ledger activity yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $entries->links() }}
    </x-adminlte-card>

@stop
