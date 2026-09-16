@extends('adminlte::page')

@section('title', 'Recipients')

@section('content_header')
    <h1>{{ $campaign->name }}</h1>
@stop

@section('content')

    @include('partials.sweetalert')
    @include('campaigns.partials.steps', ['current' => 2])

    @if($placeholders)
        <x-adminlte-card icon="bi bi-upload">
            <x-slot name="titleSlot">Upload Recipients</x-slot>

            <p class="text-body-secondary">
                This template needs merge values ({{ collect($placeholders)->map(fn ($p) => '{'.$p.'}')->implode(' ') }}),
                so upload a CSV file with a header row and a column for each — phone number included.
            </p>

            <form action="{{ route('campaigns.recipients.store', $campaign) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <input type="file" name="recipients_file" accept=".csv,.txt,.xlsx"
                        class="form-control @error('recipients_file') is-invalid @enderror" required>
                    @error('recipients_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Continue to merge fields</button>
            </form>
        </x-adminlte-card>
    @else
        <x-adminlte-card icon="bi bi-people">
            <x-slot name="titleSlot">Recipients</x-slot>

            <form action="{{ route('campaigns.recipients.store', $campaign) }}" method="post" enctype="multipart/form-data">
                @csrf

                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-paste" type="button">Paste Numbers</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-upload" type="button">Upload File</button></li>
                </ul>

                <div class="tab-content mb-3">
                    <div class="tab-pane fade show active" id="tab-paste">
                        <textarea name="recipients_text" rows="10" class="form-control @error('recipients_text') is-invalid @enderror"
                            placeholder="One number per line, or comma-separated&#10;e.g.&#10;+60123456781&#10;0123456789">{{ old('recipients_text') }}</textarea>
                        @error('recipients_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="tab-pane fade" id="tab-upload">
                        <input type="file" name="recipients_file" accept=".txt,.csv" class="form-control @error('recipients_file') is-invalid @enderror">
                        @error('recipients_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">.txt or .csv &middot; up to 100,000 recipients &middot; malformed, out-of-country and duplicate numbers are rejected.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Continue to review</button>
            </form>
        </x-adminlte-card>
    @endif

    @if($campaign->rejected_count > 0)
        <x-adminlte-card icon="bi bi-exclamation-triangle" class="mt-4">
            <x-slot name="titleSlot">Rejected Rows ({{ $campaign->rejected_count }})</x-slot>

            <table class="table table-sm">
                <thead><tr><th>Row</th><th>Value</th><th>Reason</th></tr></thead>
                <tbody>
                    @foreach(array_slice($campaign->rejected_rows ?? [], 0, 20) as $row)
                        <tr>
                            <td>{{ $row['row'] }}</td>
                            <td>{{ $row['value'] ?? '—' }}</td>
                            <td>{{ $row['reason'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-adminlte-card>
    @endif

@stop
