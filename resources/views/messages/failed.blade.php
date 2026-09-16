@extends('adminlte::page')

@section('title', 'Failed Messages')

@section('content_header')
    <h1>Failed Messages</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <x-adminlte-card icon="bi bi-x-circle">
        <x-slot name="titleSlot">Failed Messages &middot; {{ $messages->total() }} results</x-slot>

        <form method="get" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label mb-0 small">Campaign</label>
                <select name="campaign_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All campaigns</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected((int) request('campaign_id') === $campaign->id)>{{ $campaign->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <form action="{{ route('messages.resend') }}" method="post" id="resend-form">
            @csrf

            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Recipient</th>
                        <th>Reason</th>
                        <th>Credit</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td><input type="checkbox" name="message_ids[]" value="{{ $message->id }}" class="row-check"></td>
                            <td>{{ $message->maskedRecipient() }}</td>
                            <td>{{ $message->failure_reason }}</td>
                            <td>{{ $message->credit_refunded ? 'refunded' : 'charged' }}</td>
                            <td>{{ $message->submitted_at->format('d M H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-body-secondary py-4">No failed messages in range.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{ $messages->links() }}

            <div class="d-flex align-items-center gap-3 mt-3">
                <span id="selected-summary" class="small text-body-secondary"></span>
                <button type="submit" class="btn btn-warning">Resend selected</button>
            </div>
        </form>

        <p class="small text-body-secondary mt-3 mb-0">TAC and OTP messages are never resent from the portal &mdash; the client system issues a fresh code.</p>
    </x-adminlte-card>

@stop

@push('js')
<script>
    window._AdminLTE_Ready(() => {
        const selectAll = document.getElementById('select-all');
        const rows = document.querySelectorAll('.row-check');
        const summary = document.getElementById('selected-summary');

        function refresh() {
            const checked = document.querySelectorAll('.row-check:checked').length;
            summary.textContent = checked > 0 ? checked + ' selected' : '';
        }

        selectAll?.addEventListener('change', () => {
            rows.forEach((r) => { r.checked = selectAll.checked; });
            refresh();
        });

        rows.forEach((r) => r.addEventListener('change', refresh));
    });
</script>
@endpush
