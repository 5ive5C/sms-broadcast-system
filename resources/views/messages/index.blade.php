@extends('adminlte::page')

@section('title', 'Messages')

@section('content_header')
    <h1>Messages</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <x-adminlte-card>
        <x-slot name="titleSlot">Messages</x-slot>

        <form method="get" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label mb-0 small">Type</label>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach(['all', 'tac', 'transactional', 'bulk'] as $option)
                        <option value="{{ $option }}" @selected(request('type', 'all') === $option)>{{ $option === 'all' ? 'All' : $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-0 small">Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach(['all', 'delivered', 'submitted', 'failed'] as $option)
                        <option value="{{ $option }}" @selected(request('status', 'all') === $option)>{{ $option === 'all' ? 'All' : ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-0 small">Range</label>
                <select name="days" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="7" @selected(request('days', '7') === '7')>Last 7 days</option>
                    <option value="30" @selected(request('days') === '30')>Last 30 days</option>
                </select>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('messages.export', request()->query()) }}" class="btn btn-success btn-sm">Export</a>
            </div>
        </form>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Message ID</th>
                    <th>Recipient</th>
                    <th>Type</th>
                    <th>Campaign</th>
                    <th>Status</th>
                    <th>Final at</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr>
                        <td><a href="{{ route('messages.show', $message) }}">{{ $message->code }}</a></td>
                        <td>{{ $message->maskedRecipient() }}</td>
                        <td>{{ $message->lane }}</td>
                        <td>{{ $message->campaign?->name ?? '—' }}</td>
                        <td>
                            @php($theme = ['delivered' => 'green', 'submitted' => 'slate', 'failed' => 'red'][$message->status])
                            <span class="pill pill-{{ $theme }}">{{ ucfirst($message->status) }}</span>
                        </td>
                        <td>{{ $message->final_at?->format('d M H:i') ?? 'awaiting telco' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-body-secondary py-4">No messages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $messages->links() }}

        <p class="small text-body-secondary mt-2 mb-0">Retention 12 months &middot; TAC content not stored in reports.</p>
    </x-adminlte-card>

@stop
