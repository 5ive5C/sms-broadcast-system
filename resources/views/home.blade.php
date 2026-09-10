@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')

    {{-- Stat cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box theme="primary" title="1,204" text="Messages Sent Today"
                icon="bi bi-send" url="#" url-text="View report" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box theme="success" title="98.4%" text="Delivery Rate"
                icon="bi bi-check-circle" url="#" url-text="View report" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box theme="warning" title="12" text="Pending Top-Ups"
                icon="bi bi-hourglass-split" url="#" url-text="Review queue" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box theme="danger" title="3" text="Failed Campaigns"
                icon="bi bi-exclamation-triangle" url="#" url-text="Investigate" />
        </div>
    </div>

    <div class="row">
        {{-- Recent campaigns table --}}
        <div class="col-lg-8">
            <x-adminlte-card title="Recent Campaigns" theme="primary" icon="bi bi-megaphone" collapsible>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Client</th>
                                <th>Recipients</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Promo Sept</td>
                                <td>Demo Client</td>
                                <td>4,500</td>
                                <td><span class="badge text-bg-success">Delivered</span></td>
                            </tr>
                            <tr>
                                <td>OTP Batch</td>
                                <td>Demo Client</td>
                                <td>980</td>
                                <td><span class="badge text-bg-primary">Sending</span></td>
                            </tr>
                            <tr>
                                <td>Reminder Q3</td>
                                <td>Acme Corp</td>
                                <td>2,120</td>
                                <td><span class="badge text-bg-warning">Queued</span></td>
                            </tr>
                            <tr>
                                <td>Alert Broadcast</td>
                                <td>Acme Corp</td>
                                <td>310</td>
                                <td><span class="badge text-bg-danger">Failed</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        </div>

        {{-- Wallet / balance --}}
        <div class="col-lg-4">
            <x-adminlte-card title="Wallet Balance" theme="light" icon="bi bi-wallet2">
                <h2 class="mb-1" style="color: var(--bs-primary);">RM 12,450.00</h2>
                <p class="text-body-secondary mb-3">Demo Client &middot; Standard Tier</p>

                <x-adminlte-progress-group label="Credits used" :value="68" :max="100" theme="primary" />
                <x-adminlte-progress-group label="Delivery success" :value="98" :max="100" theme="success" />
                <x-adminlte-progress-group label="Retry backlog" :value="24" :max="100" theme="warning" />

                <button type="button" class="btn btn-primary w-100 mt-2">Request Top-Up</button>
            </x-adminlte-card>

            <x-adminlte-card title="Buttons &amp; Badges" theme="light">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-primary">Primary</button>
                    <button type="button" class="btn btn-success">Success</button>
                    <button type="button" class="btn btn-warning">Warning</button>
                    <button type="button" class="btn btn-danger">Danger</button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge text-bg-primary">Primary</span>
                    <span class="badge text-bg-success">Success</span>
                    <span class="badge text-bg-warning">Warning</span>
                    <span class="badge text-bg-danger">Danger</span>
                </div>
            </x-adminlte-card>
        </div>
    </div>

@stop
