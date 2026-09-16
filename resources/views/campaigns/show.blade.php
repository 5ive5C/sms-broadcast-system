@extends('adminlte::page')

@section('title', $campaign->name)

@section('content_header')
    <h1>{{ $campaign->name }}</h1>
    <div class="text-body-secondary small">
        @if($campaign->launched_at)
            Launched {{ $campaign->launched_at->format('H:i') }} by {{ $campaign->launcher?->name }} &middot; lane {{ $campaign->lane }}
        @else
            {{ ucfirst($campaign->status) }} &middot; lane {{ $campaign->lane }}
        @endif
    </div>
@stop

@section('content')

    @include('partials.sweetalert')

    <x-adminlte-card icon="bi bi-graph-up">
        <x-slot name="titleSlot">Dispatched</x-slot>

        @php($dispatched = $campaign->delivered_count + $campaign->submitted_count + $campaign->failed_count)

        <div class="mb-2">{{ number_format($dispatched) }} of {{ number_format($campaign->recipient_count) }} &middot; {{ $campaign->progressPercent() }}%</div>
        <div class="progress mb-4" style="height: 10px;">
            <div class="progress-bar bg-success" style="width: {{ $campaign->progressPercent() }}%"></div>
        </div>

        <div class="row text-center">
            <div class="col">
                <div class="fs-4 fw-bold text-success">{{ number_format($campaign->delivered_count) }}</div>
                <div class="small text-body-secondary">Delivered</div>
            </div>
            <div class="col">
                <div class="fs-4 fw-bold text-warning">{{ number_format($campaign->submitted_count) }}</div>
                <div class="small text-body-secondary">Submitted</div>
            </div>
            <div class="col">
                <div class="fs-4 fw-bold text-danger">{{ number_format($campaign->failed_count) }}</div>
                <div class="small text-body-secondary">Failed</div>
            </div>
            <div class="col">
                <div class="fs-4 fw-bold">{{ number_format($campaign->credits_spent) }}</div>
                <div class="small text-body-secondary">Credits spent</div>
            </div>
        </div>
    </x-adminlte-card>

    <x-adminlte-card icon="bi bi-clock-history">
        <x-slot name="titleSlot">Timeline</x-slot>

        <table class="table table-sm mb-0">
            <tbody>
                @if($campaign->launched_at)
                    <tr>
                        <td class="text-body-secondary" style="width: 100px;">{{ $campaign->launched_at->format('H:i') }}</td>
                        <td class="fw-semibold">Launched</td>
                        <td>{{ number_format($campaign->recipient_count) }} messages queued on lane {{ $campaign->lane }}</td>
                    </tr>
                @endif
                @if($campaign->failed_count > 0)
                    <tr>
                        <td class="text-body-secondary">{{ $campaign->launched_at?->addMinutes(2)->format('H:i') }}</td>
                        <td class="fw-semibold">Throttled</td>
                        <td>{{ $campaign->lane }} lane rate reduced</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </x-adminlte-card>

@stop
