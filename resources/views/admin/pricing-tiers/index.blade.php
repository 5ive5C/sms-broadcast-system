@extends('adminlte::page')

@section('title', 'Pricing Tiers')

@section('content_header')
    <h1>Pricing Tiers</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <x-adminlte-card icon="bi bi-tags">
        <x-slot name="titleSlot">Pricing Tiers</x-slot>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Top-up size</th>
                    <th>Price / credit</th>
                    <th>Total</th>
                    <th>Clients on tier</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($tiers as $tier)
                    <tr>
                        <td class="fw-semibold">{{ number_format($tier->min_credits) }} credits</td>
                        <td>RM{{ number_format($tier->price_per_credit, 3) }}</td>
                        <td>RM{{ number_format($tier->min_credits * $tier->price_per_credit) }}</td>
                        <td>{{ $tier->clients_count }}</td>
                        <td>
                            <form action="{{ route('pricing-tiers.update', $tier) }}" method="post" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="min_credits" value="{{ $tier->min_credits }}">
                                <input type="hidden" name="price_per_credit" value="{{ $tier->price_per_credit }}">
                                <input type="hidden" name="is_active" value="{{ $tier->is_active ? 0 : 1 }}">
                                <button type="submit" class="btn btn-link p-0 border-0 {{ $tier->is_active ? 'text-success' : 'text-warning' }}">
                                    {{ $tier->is_active ? 'Active' : 'Draft' }}
                                </button>
                            </form>
                        </td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-adminlte-card>

    <x-adminlte-card icon="bi bi-plus-circle">
        <x-slot name="titleSlot">Add Tier</x-slot>

        <form action="{{ route('pricing-tiers.store') }}" method="post" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-3">
                <label for="min_credits" class="form-label">Top-up size</label>
                <input type="number" name="min_credits" id="min_credits" min="1"
                    class="form-control @error('min_credits') is-invalid @enderror" value="{{ old('min_credits') }}" required>
                @error('min_credits')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label for="price_per_credit" class="form-label">Price per credit</label>
                <input type="number" step="0.0001" name="price_per_credit" id="price_per_credit"
                    class="form-control @error('price_per_credit') is-invalid @enderror" value="{{ old('price_per_credit') }}" required>
                @error('price_per_credit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" checked>
                    <label for="is_active" class="form-check-label">Active</label>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Save tier</button>
            </div>
        </form>
    </x-adminlte-card>

@stop
