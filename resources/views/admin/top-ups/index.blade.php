@extends('adminlte::page')

@section('title', 'Top-Up Approvals')

@section('content_header')
    <h1>Top-Up Requests</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <div class="d-flex gap-2 mb-3">
        <span class="pill pill-gray">Pending &middot; {{ $pendingCount }}</span>
        <span class="pill pill-gray">Approved &middot; {{ $approvedCount }}</span>
    </div>

    <x-adminlte-card>
        <x-slot name="titleSlot">Top-Up Requests</x-slot>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Reference</th>
                    <th>Credits</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $topUp)
                    <tr>
                        <td class="fw-semibold">{{ $topUp->client->name }}</td>
                        <td>{{ $topUp->reference() }}</td>
                        <td>{{ number_format($topUp->credits) }}</td>
                        <td>RM{{ number_format($topUp->total_amount, 2) }}</td>
                        <td>
                            @if($topUp->status === 'pending')
                                <span class="pill pill-amber">Pending</span>
                            @elseif($topUp->status === 'approved')
                                <span class="pill pill-green">Approved</span>
                                <div class="small text-body-secondary">by {{ $topUp->approver?->name }}</div>
                            @else
                                <span class="pill pill-red">Rejected</span>
                            @endif
                        </td>
                        <td>
                            @if($topUp->isPending())
                                <form action="{{ route('top-ups.approve', $topUp) }}" method="post" class="d-inline"
                                    data-confirm="Approve {{ $topUp->reference() }}? Wallet moves from {{ number_format($topUp->client->wallet?->balance ?? 0) }} to {{ number_format(($topUp->client->wallet?->balance ?? 0) + $topUp->credits) }} credits.">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form action="{{ route('top-ups.reject', $topUp) }}" method="post" class="d-inline ms-2"
                                    data-confirm="Reject {{ $topUp->reference() }}?">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                                </form>
                            @else
                                @if($topUp->slip_path)
                                    <a href="{{ asset('storage/'.$topUp->slip_path) }}" target="_blank">Slip</a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-adminlte-card>

@stop
