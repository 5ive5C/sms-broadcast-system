@extends('adminlte::page')

@section('title', 'Merge Fields')

@section('content_header')
    <h1>{{ $campaign->name }}</h1>
@stop

@section('content')

    @include('partials.sweetalert')
    @include('campaigns.partials.steps', ['current' => 3])

    <x-adminlte-card icon="bi bi-diagram-3">
        <x-slot name="titleSlot">Map Columns to Placeholders</x-slot>

        <p class="text-body-secondary">
            {{ $campaign->recipient_file_name }} &middot; {{ $rowCount }} rows
        </p>

        <form action="{{ route('campaigns.merge.store', $campaign) }}" method="post">
            @csrf

            <div class="mb-3">
                <label class="form-label">phone</label>
                <select name="phone_column" class="form-select @error('phone_column') is-invalid @enderror" required>
                    <option value="">Select column&hellip;</option>
                    @foreach($header as $i => $col)
                        <option value="{{ $i }}">{{ chr(65 + $i) }} &mdash; {{ $col }}</option>
                    @endforeach
                </select>
                @error('phone_column')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @foreach($placeholders as $placeholder)
                <div class="mb-3">
                    <label class="form-label">{ {{ $placeholder }} }</label>
                    <select name="columns[{{ $placeholder }}]" class="form-select" required>
                        <option value="">Select column&hellip;</option>
                        @foreach($header as $i => $col)
                            <option value="{{ $i }}">{{ chr(65 + $i) }} &mdash; {{ $col }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Continue to review</button>
        </form>
    </x-adminlte-card>

@stop
