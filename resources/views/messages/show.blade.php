@extends('adminlte::page')

@section('title', $message->code)

@section('content_header')
    <h1>{{ $message->code }}</h1>
    @php($theme = ['delivered' => 'green', 'submitted' => 'slate', 'failed' => 'red'][$message->status])
    <span class="pill pill-{{ $theme }}">{{ ucfirst($message->status) }}</span>
@stop

@section('content')

    @include('partials.sweetalert')

    <div class="row">
        <div class="col-lg-6 mb-4">
            <x-adminlte-card>
                <x-slot name="titleSlot">Details</x-slot>

                <dl class="row mb-0">
                    <dt class="col-5">Recipient</dt>
                    <dd class="col-7">{{ $message->maskedRecipient() }}</dd>
                    <dt class="col-5">Type / lane</dt>
                    <dd class="col-7">{{ $message->lane }}</dd>
                    <dt class="col-5">Campaign</dt>
                    <dd class="col-7">{{ $message->campaign?->name ?? '—' }}</dd>
                    <dt class="col-5">Encoding / parts</dt>
                    <dd class="col-7">{{ $message->encoding === 'gsm7' ? 'GSM 7-bit' : 'Unicode' }} &middot; {{ $message->parts }} part(s)</dd>
                    <dt class="col-5">Credits</dt>
                    <dd class="col-7">{{ $message->credit_charged }} debited{{ $message->credit_refunded ? ', '.$message->credit_refunded.' refunded' : '' }}</dd>
                </dl>
            </x-adminlte-card>

            <x-adminlte-card>
                <x-slot name="titleSlot">Content</x-slot>
                <p class="mb-0">{{ $message->lane === 'tac' ? '(TAC content is not stored)' : $message->content }}</p>
            </x-adminlte-card>
        </div>

        <div class="col-lg-6 mb-4">
            <x-adminlte-card>
                <x-slot name="titleSlot">Attempt History</x-slot>

                <table class="table table-sm mb-0">
                    <thead><tr><th>#</th><th>Time</th><th>Result</th></tr></thead>
                    <tbody>
                        @forelse($message->attempts as $attempt)
                            <tr>
                                <td>{{ $attempt->sequence }}</td>
                                <td>{{ $attempt->attempted_at->format('H:i:s') }}</td>
                                <td>{{ $attempt->result }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-body-secondary text-center py-3">No attempt history recorded.</td></tr>
                        @endforelse
                        @if($message->final_at)
                            <tr>
                                <td>—</td>
                                <td>{{ $message->final_at->format('H:i:s') }}</td>
                                <td>
                                    Marked {{ ucfirst($message->status) }}
                                    @if($message->credit_refunded)
                                        &middot; {{ $message->credit_refunded }} credit refunded
                                    @endif
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </x-adminlte-card>

            @if($message->status === 'failed' && $message->lane !== 'tac')
                <form action="{{ route('messages.resend') }}" method="post">
                    @csrf
                    <input type="hidden" name="message_ids[]" value="{{ $message->id }}">
                    <button type="submit" class="btn btn-warning">Resend this message</button>
                </form>
            @endif
        </div>
    </div>

@stop
