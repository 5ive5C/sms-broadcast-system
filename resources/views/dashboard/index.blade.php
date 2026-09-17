@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>{{ $client->name }}</h1>
    <div class="text-body-secondary small">{{ now()->format('d M Y') }} &middot; updated just now</div>
@stop

@section('content')

    @include('partials.sweetalert')

    <div class="row mb-4">
        <div class="col-md-3">
            <x-adminlte-info-box title="Sent today" text="{{ number_format($sentToday) }}" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Delivered" text="{{ $deliveryRate }}%" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Awaiting status" text="{{ number_format($awaiting) }}" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Balance" text="{{ number_format($wallet->balance) }}" />
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <x-adminlte-card>
                <x-slot name="titleSlot">Volume by Hour</x-slot>

                <canvas id="volume-chart" height="90"></canvas>
            </x-adminlte-card>
        </div>

        <div class="col-lg-5 mb-4">
            <x-adminlte-card>
                <x-slot name="titleSlot">Needs Attention</x-slot>

                <div class="d-flex flex-column gap-2">
                    @if($runwayDays)
                        <div class="p-3 rounded-3 pill-amber-box">Balance covers about {{ $runwayDays }} days at current volume.</div>
                    @endif
                    @if($failedLast7Days > 0)
                        <div class="p-3 rounded-3 pill-red-box">{{ number_format($failedLast7Days) }} failed messages in the last 7 days.</div>
                    @endif
                    @if($sendingCampaign)
                        <div class="p-3 rounded-3 pill-gray-box">
                            Campaign "{{ $sendingCampaign->name }}" is {{ $sendingCampaign->progressPercent() }}% dispatched.
                        </div>
                    @endif
                    @if(! $runwayDays && $failedLast7Days === 0 && ! $sendingCampaign)
                        <div class="text-body-secondary">Nothing needs attention right now.</div>
                    @endif
                </div>
            </x-adminlte-card>
        </div>
    </div>

@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js" integrity="" crossorigin="anonymous" defer></script>
<script>
    window._AdminLTE_Ready(() => {
        const hours = @json($volumeByHour);
        const labels = Array.from({length: 24}, (_, h) => String(h).padStart(2, '0') + ':00');
        const data = labels.map((_, h) => hours[h] ?? 0);

        const ctx = document.getElementById('volume-chart');
        if (ctx && window.Chart) {
            // Three-step intensity per the deck's chart spec: light/mid/peak
            // purple depending on how tall the bar is relative to the max.
            const max = Math.max(...data, 1);
            const colors = data.map((v) => {
                const ratio = v / max;
                if (ratio >= 0.75) return '#6f5cd8';
                if (ratio >= 0.4) return '#a99bf0';
                return '#d8d3f6';
            });

            new Chart(ctx, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Messages', data, backgroundColor: colors, borderRadius: 3 }] },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
            });
        }
    });
</script>
@endpush
