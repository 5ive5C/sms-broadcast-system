@extends('adminlte::page')

@section('title', 'API Keys')

@section('content_header')
    <h1>API Keys</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    @if($plainSecret)
        <x-adminlte-alert theme="warning" title="New key generated">
            The secret is displayed once. Store it before closing this panel.
            <div class="mt-2">
                <code class="user-select-all">{{ $plainSecret }}</code>
            </div>
        </x-adminlte-alert>
    @endif

    <x-adminlte-card icon="bi bi-key">
        <x-slot name="titleSlot">Keys</x-slot>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Label</th>
                    <th>IP whitelist</th>
                    <th>Last used</th>
                    <th>State</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($apiKeys as $apiKey)
                    <tr>
                        <td><code>{{ $apiKey->key }}••••</code></td>
                        <td>{{ $apiKey->name }}</td>
                        <td>{{ $apiKey->ip_whitelist ? implode(', ', $apiKey->ip_whitelist) : 'none' }}</td>
                        <td>{{ $apiKey->last_used_at?->diffForHumans() ?? 'never' }}</td>
                        <td>
                            @if($apiKey->isRevoked())
                                <span class="text-danger fw-semibold">Revoked</span>
                            @else
                                <span class="text-success fw-semibold">Active</span>
                            @endif
                        </td>
                        <td>
                            @unless($apiKey->isRevoked())
                                <form action="{{ route('api-keys.revoke', $apiKey) }}" method="post"
                                    data-confirm="Revoke key &quot;{{ $apiKey->name }}&quot;? Anything using it stops working immediately.">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-link p-0 border-0 text-danger">Revoke</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-body-secondary py-4">No API keys yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-adminlte-card>

    <x-adminlte-card icon="bi bi-plus-circle">
        <x-slot name="titleSlot">Generate New Key</x-slot>

        <form action="{{ route('api-keys.store') }}" method="post" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label for="name" class="form-label">Label</label>
                <input type="text" name="name" id="name"
                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                    placeholder="e.g. Core banking" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-5">
                <label for="ip_whitelist" class="form-label">IP whitelist <span class="text-body-secondary">(optional)</span></label>
                <input type="text" name="ip_whitelist" id="ip_whitelist"
                    class="form-control @error('ip_whitelist') is-invalid @enderror" value="{{ old('ip_whitelist') }}"
                    placeholder="e.g. 203.115.4.0/24, comma separated">
                @error('ip_whitelist')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Generate key</button>
            </div>
        </form>
    </x-adminlte-card>

@stop
