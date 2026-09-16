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
            <x-adminlte-info-box title="Sent today" text="{{ number_format($sentToday) }}" icon="bi bi-send" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Delivered" text="{{ $deliveryRate }}%" icon="bi bi-check-circle" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Awaiting status" text="{{ number_format($awaiting) }}" icon="bi bi-hourglass-split" />
        </div>
        <div class="col-md-3">
            <x-adminlte-info-box title="Balance" text="{{ number_format($wallet->balance) }}" icon="bi bi-wallet2" />
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <x-adminlte-card icon="bi bi-bar-chart">
                <x-slot name="titleSlot">Volume by Hour</x-slot>

                <canvas id="volume-chart" height="90"></canvas>
            </x-adminlte-card>
        </div>

        <div class="col-lg-5 mb-4">
            <x-adminlte-card icon="bi bi-exclamation-triangle">
                <x-slot name="titleSlot">Needs Attention</x-slot>

                <ul class="list-unstyled mb-0">
                    @if($runwayDays)
                        <li class="mb-2"><i class="bi bi-dot"></i> Balance covers about {{ $runwayDays }} days at current volume.</li>
                    @endif
                    @if($failedLast7Days > 0)
                        <li class="mb-2"><i class="bi bi-dot"></i> {{ number_format($failedLast7Days) }} failed messages in the last 7 days.</li>
                    @endif
                    @if($sendingCampaign)
                        <li class="mb-2">
                            <i class="bi bi-dot"></i> Campaign "{{ $sendingCampaign->name }}" is {{ $sendingCampaign->progressPercent() }}% dispatched.
                        </li>
                    @endif
                    @if(! $runwayDays && $failedLast7Days === 0 && ! $sendingCampaign)
                        <li class="text-body-secondary">Nothing needs attention right now.</li>
                    @endif
                </ul>
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
            new Chart(ctx, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Messages', data, backgroundColor: '#0d6efd' }] },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
            });
        }
    });
</script>
@endpush
