@extends('adminlte::page')
@section('content')

    @include('partials.sweetalert')

    <style>
        #clients-table thead th {
            background-color: var(--bs-primary);
            color: #fff;
            --dt-order-arrow_color: rgba(255, 255, 255, 0.5);
            --dt-order-arrow_color-current: #fff;
        }
        #clients-table thead th.dt-orderable-asc:hover,
        #clients-table thead th.dt-orderable-desc:hover {
            background-color: var(--bs-primary);
            color: #fff;
        }
    </style>

    <x-adminlte-card>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">List of Clients</h5>

            <a href="{{ route('clients.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                Add Client
                <span class="border-start ps-2"><i class="bi bi-plus-lg"></i></span>
            </a>
        </div>

        {{-- Register a custom Datatables button that just re-runs the ajax
             request, before the table below initializes and references it
             by name. --}}
        @prepend('js')
        <script>
            window._AdminLTE_Ready(() => {
                if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.DataTable === 'undefined') {
                    return;
                }

                window.jQuery.fn.dataTable.ext.buttons.reload = {
                    text: '<i class="bi bi-arrow-clockwise"></i>',
                    className: 'btn-outline-secondary',
                    titleAttr: 'Refresh',
                    action: function (e, dt) {
                        dt.ajax.reload(null, false);
                    },
                };
            });
        </script>
        @endprepend

        <x-adminlte-datatable id="clients-table"
            :heads="[
                'ID', 'Name', 'Slug', 'Sender ID', 'Pricing Tier',
                'Status', 'Created At', ['label' => 'Actions', 'no-export' => true],
            ]"
            striped hoverable with-buttons
            :config="[
                'processing' => true,
                'serverSide' => true,
                'ajax' => route('clients.index'),
                'lengthMenu' => [[10, 25, 50, -1], [10, 25, 50, 'All']],
                'layout' => [
                    'topStart' => ['buttons', 'pageLength'],
                    'topEnd' => 'search',
                    'bottomStart' => 'info',
                    'bottomEnd' => 'paging',
                ],
                'columns' => [
                    ['data' => 'id', 'name' => 'id'],
                    ['data' => 'name', 'name' => 'name'],
                    ['data' => 'slug', 'name' => 'slug'],
                    ['data' => 'sender_id', 'name' => 'sender_id'],
                    ['data' => 'pricing_tier', 'name' => 'pricing_tier', 'orderable' => false, 'searchable' => false],
                    ['data' => 'status', 'name' => 'status', 'orderable' => false, 'searchable' => false],
                    ['data' => 'created_at', 'name' => 'created_at'],
                    ['data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
                ],
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn-success', 'text' => '<i class=\'bi bi-file-earmark-excel\'></i>', 'titleAttr' => 'Excel', 'exportOptions' => ['columns' => ':not([dt-no-export])']],
                    ['extend' => 'csv', 'className' => 'btn-primary', 'text' => '<i class=\'bi bi-filetype-csv\'></i>', 'titleAttr' => 'CSV', 'exportOptions' => ['columns' => ':not([dt-no-export])']],
                    ['extend' => 'pdf', 'className' => 'btn-danger', 'text' => '<i class=\'bi bi-file-earmark-pdf\'></i>', 'titleAttr' => 'PDF', 'exportOptions' => ['columns' => ':not([dt-no-export])']],
                    ['extend' => 'print', 'className' => 'btn-dark', 'text' => '<i class=\'bi bi-printer\'></i>', 'titleAttr' => 'Print', 'exportOptions' => ['columns' => ':not([dt-no-export])']],
                    ['extend' => 'reload'],
                ],
            ]" />
    </x-adminlte-card>

@stop
