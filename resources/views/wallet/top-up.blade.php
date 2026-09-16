@extends('adminlte::page')

@section('title', 'Request Top-Up')

@section('content_header')
    <h1>Request Top-Up</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <div class="row">
        <div class="col-lg-7 mb-4">
            <x-adminlte-card icon="bi bi-cash-coin">
                <x-slot name="titleSlot">Request Top-Up</x-slot>

                <form action="{{ route('wallet.top-up.store') }}" method="post" enctype="multipart/form-data" id="topup-form">
                    @csrf

                    <div class="mb-3">
                        <label for="credits" class="form-label">Credits to purchase</label>
                        <input type="number" name="credits" id="credits" min="100" step="1"
                            class="form-control @error('credits') is-invalid @enderror" value="{{ old('credits', 100000) }}" required>
                        <div class="btn-group mt-2" role="group">
                            @foreach([1000, 10000, 100000] as $amount)
                                <button type="button" class="btn btn-sm btn-outline-primary quick-amount" data-amount="{{ $amount }}">{{ number_format($amount) }}</button>
                            @endforeach
                        </div>
                        @error('credits')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="slip" class="form-label">Payment slip</label>
                        <input type="file" name="slip" id="slip" accept=".pdf,.jpg,.jpeg,.png"
                            class="form-control @error('slip') is-invalid @enderror" required>
                        <div class="form-text">Bank transfer slip (PDF or image).</div>
                        @error('slip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Submit request</button>
                </form>
            </x-adminlte-card>
        </div>

        <div class="col-lg-5 mb-4">
            <x-adminlte-card icon="bi bi-receipt">
                <x-slot name="titleSlot">Summary</x-slot>

                @php($tier = $tiers->sortByDesc('min_credits')->first(fn($t) => $t->min_credits <= 100000) ?? $tiers->sortBy('min_credits')->first())

                <dl class="row mb-0">
                    <dt class="col-6">Tier applied</dt>
                    <dd class="col-6" id="summary-tier">RM{{ $tier ? number_format($tier->price_per_credit, 3) : '0.120' }} / credit</dd>
                    <dt class="col-6">Total payable</dt>
                    <dd class="col-6" id="summary-total">RM{{ $tier ? number_format(100000 * $tier->price_per_credit, 2) : '0.00' }}</dd>
                    <dt class="col-6">Balance after approval</dt>
                    <dd class="col-6" id="summary-balance">{{ number_format($wallet->balance + 100000) }}</dd>
                </dl>
                <p class="small text-body-secondary mt-3 mb-0">Credit is added only after internal admin confirms payment.</p>
            </x-adminlte-card>
        </div>
    </div>

@stop

@push('js')
<script>
    window._AdminLTE_Ready(() => {
        const tiers = @json($tiers->map(fn($t) => ['min' => $t->min_credits, 'price' => (float) $t->price_per_credit])->values());
        const creditsEl = document.getElementById('credits');
        const balance = {{ (float) $wallet->balance }};

        function applicableTier(credits) {
            const sorted = [...tiers].sort((a, b) => b.min - a.min);
            return sorted.find((t) => credits >= t.min) || sorted[sorted.length - 1];
        }

        function refresh() {
            const credits = parseInt(creditsEl.value, 10) || 0;
            const tier = applicableTier(credits);
            if (! tier) return;

            document.getElementById('summary-tier').textContent = 'RM' + tier.price.toFixed(3) + ' / credit';
            document.getElementById('summary-total').textContent = 'RM' + (credits * tier.price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('summary-balance').textContent = (balance + credits).toLocaleString();
        }

        document.querySelectorAll('.quick-amount').forEach((btn) => {
            btn.addEventListener('click', () => {
                creditsEl.value = btn.dataset.amount;
                refresh();
            });
        });

        creditsEl.addEventListener('input', refresh);
        refresh();
    });
</script>
@endpush
