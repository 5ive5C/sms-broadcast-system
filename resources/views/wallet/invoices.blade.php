@extends('adminlte::page')

@section('title', 'Invoices')

@section('content_header')
    <h1>Invoices</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <x-adminlte-card>
        <x-slot name="titleSlot">Invoices</x-slot>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="fw-semibold">{{ $invoice->invoice_no }}</td>
                        <td>{{ $invoice->invoice_date->format('d M') }}</td>
                        <td>RM{{ number_format($invoice->total, 2) }}</td>
                        <td>
                            @if($invoice->status === 'paid')
                                <span class="pill pill-green">Paid</span>
                            @else
                                <span class="pill pill-amber">Unpaid</span>
                            @endif
                        </td>
                        <td>
                            <a href="#" class="text-primary" title="Download PDF" data-bs-toggle="tooltip">
                                <i class="bi bi-download"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-body-secondary py-4">No invoices yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-adminlte-card>

    @if($invoices->isNotEmpty())
        @php($invoice = $invoices->first())
        <x-adminlte-card>
            <x-slot name="titleSlot">{{ $invoice->invoice_no }} &middot; {{ $invoice->invoice_date->format('d M Y') }}</x-slot>

            <dl class="row mb-0">
                <dt class="col-8">{{ number_format($invoice->credits) }} credits @ RM{{ number_format($invoice->price_per_credit, 3) }}</dt>
                <dd class="col-4 text-end">RM{{ number_format($invoice->amount, 2) }}</dd>
                <dt class="col-8">SST</dt>
                <dd class="col-4 text-end">RM{{ number_format($invoice->sst, 2) }}</dd>
                <dt class="col-8 fw-bold">Total</dt>
                <dd class="col-4 text-end fw-bold">RM{{ number_format($invoice->total, 2) }}</dd>
            </dl>
            <p class="small text-body-secondary mt-3 mb-0">
                Pay to: SMS Broadcast Sdn Bhd &middot; Ref {{ $invoice->topUpRequest?->reference() }}
            </p>
        </x-adminlte-card>
    @endif

@stop
