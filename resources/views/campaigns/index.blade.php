@extends('adminlte::page')

@section('title', 'Campaigns')

@section('content_header')
    <h1>Campaigns</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <x-adminlte-card>
        <x-slot name="titleSlot">Campaigns</x-slot>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="get" class="d-flex align-items-center gap-2">
                <label class="form-label mb-0">Status:</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach(['all', 'draft', 'scheduled', 'sending', 'completed', 'cancelled'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
                New campaign <i class="bi bi-plus-lg"></i>
            </a>
        </div>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Campaign</th>
                    <th>Created by</th>
                    <th>Recipients</th>
                    <th>Delivered</th>
                    <th>Failed</th>
                    <th>State</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $campaign)
                    <tr>
                        <td><a href="{{ $campaign->isDraft() ? route('campaigns.review', $campaign) : route('campaigns.show', $campaign) }}" class="fw-semibold">{{ $campaign->name }}</a></td>
                        <td>{{ $campaign->creator?->name ?? '—' }}</td>
                        <td>{{ number_format($campaign->recipient_count) }}</td>
                        <td>{{ $campaign->status === 'draft' || $campaign->status === 'scheduled' ? '—' : number_format($campaign->delivered_count) }}</td>
                        <td>{{ $campaign->status === 'draft' || $campaign->status === 'scheduled' ? '—' : number_format($campaign->failed_count) }}</td>
                        <td>
                            @php($theme = ['draft' => 'gray', 'scheduled' => 'amber', 'sending' => 'indigo', 'completed' => 'green', 'cancelled' => 'red'][$campaign->status])
                            <span class="pill pill-{{ $theme }}">{{ ucfirst($campaign->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-body-secondary py-4">No campaigns yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $campaigns->links() }}
    </x-adminlte-card>

@stop
