@extends('adminlte::page')

@section('title', 'Audit Log')

@section('content_header')
    <h1>Audit Log</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <x-adminlte-card icon="bi bi-journal-text">
        <x-slot name="titleSlot">Audit Log</x-slot>

        <form method="get" class="row g-2 align-items-end mb-3">
            <div class="col-auto">
                <label class="form-label mb-0 small">Client</label>
                <select name="client_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All clients</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" @selected((int) request('client_id') === $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label mb-0 small">Action</label>
                <select name="action" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M H:i') }}</td>
                        <td>{{ $log->actor_name }}</td>
                        <td class="fw-semibold">{{ $log->action }}</td>
                        <td>{{ $log->client?->name ? $log->client->name.' · ' : '' }}{{ $log->detail }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-body-secondary py-4">No audit entries.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $logs->links() }}

        <p class="small text-body-secondary mt-2 mb-0">Immutable. Client admins see their own organisation's entries; internal admins see all.</p>
    </x-adminlte-card>

@stop
