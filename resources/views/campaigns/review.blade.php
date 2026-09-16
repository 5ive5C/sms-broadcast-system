@extends('adminlte::page')

@section('title', 'Confirm and Launch')

@section('content_header')
    <h1>{{ $campaign->name }}</h1>
@stop

@section('content')

    @include('partials.sweetalert')
    @include('campaigns.partials.steps', ['current' => 4])

    <div class="row">
        <div class="col-lg-7 mb-4">
            <x-adminlte-card icon="bi bi-chat-left-text">
                <x-slot name="titleSlot">Message</x-slot>

                <div class="border rounded-3 p-3 bg-body-tertiary mb-3">
                    <div class="d-inline-block px-3 py-2 rounded-4 bg-primary text-white" style="max-width: 100%; word-break: break-word;">
                        <span class="small">{{ $preview }}</span>
                    </div>
                </div>

                <dl class="row mb-0">
                    <dt class="col-4">Recipients</dt>
                    <dd class="col-8">{{ number_format($recipientCount) }}</dd>
                    <dt class="col-4">Credits required</dt>
                    <dd class="col-8">{{ number_format($creditsRequired) }}</dd>
                    <dt class="col-4">Balance after send</dt>
                    <dd class="col-8">{{ number_format($balanceAfter) }}</dd>
                    <dt class="col-4">Priority lane</dt>
                    <dd class="col-8">{{ $campaign->lane }}</dd>
                </dl>

                @if($campaign->rejected_count > 0)
                    <p class="small text-warning mt-3 mb-0">{{ $campaign->rejected_count }} rows were rejected at upload. They will not be sent.</p>
                @endif
            </x-adminlte-card>
        </div>

        <div class="col-lg-5 mb-4">
            <x-adminlte-card icon="bi bi-rocket-takeoff">
                <x-slot name="titleSlot">Send</x-slot>

                @if($balanceAfter < 0)
                    <div class="alert alert-danger">Not enough balance for this send. Top up before launching.</div>
                @endif

                <form action="{{ route('campaigns.launch', $campaign) }}" method="post">
                    @csrf

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="radio" name="when" value="now" id="when-now" class="form-check-input" checked
                                onchange="document.getElementById('scheduled_at').disabled = true">
                            <label for="when-now" class="form-check-label">Immediately</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" name="when" value="later" id="when-later" class="form-check-input"
                                onchange="document.getElementById('scheduled_at').disabled = false">
                            <label for="when-later" class="form-check-label">Schedule for later</label>
                        </div>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control mt-2" disabled>
                    </div>

                    <p class="small text-body-secondary">Dispatch is throttled to protect the TAC lane.</p>

                    <button type="submit" class="btn btn-primary w-100" {{ $balanceAfter < 0 ? 'disabled' : '' }}>Launch campaign</button>
                </form>

                <form action="{{ route('campaigns.save-draft', $campaign) }}" method="post" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">Save as draft</button>
                </form>
            </x-adminlte-card>
        </div>
    </div>

@stop
