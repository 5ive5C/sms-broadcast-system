@extends('adminlte::page')
@section('content')

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('users.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            Add User
            <span class="border-start ps-2"><i class="bi bi-plus-lg"></i></span>
        </a>
    </div>

    <style>
        #users-table thead th {
            background-color: var(--bs-primary);
            color: #fff;
            --dt-order-arrow_color: rgba(255, 255, 255, 0.5);
            --dt-order-arrow_color-current: #fff;
        }
        #users-table thead th.dt-orderable-asc:hover,
        #users-table thead th.dt-orderable-desc:hover {
            background-color: var(--bs-primary);
            color: #fff;
        }
    </style>

    <x-adminlte-card>
        <h5 class="fw-bold mb-3">List of Users</h5>

        {{-- Register a custom Datatables button that just re-runs the ajax
             request, before the table below initializes and references it
             by name. --}}
        @push('js')
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
        @endpush

        <x-adminlte-datatable id="users-table"
            :heads="[
                'ID', 'Name', 'Email', 'Role', 'Client',
                'Created At', 'Updated At',
                'Status', ['label' => 'Actions', 'no-export' => true],
            ]"
            striped hoverable with-buttons
            :config="[
                'processing' => true,
                'serverSide' => true,
                'ajax' => route('users.index'),
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
                    ['data' => 'email', 'name' => 'email'],
                    ['data' => 'role', 'name' => 'role', 'orderable' => false, 'searchable' => false],
                    ['data' => 'client', 'name' => 'client', 'orderable' => false, 'searchable' => false],
                    // ['data' => 'created_by', 'name' => 'created_by', 'orderable' => false, 'searchable' => false],
                    ['data' => 'created_at', 'name' => 'created_at'],
                    // ['data' => 'updated_by'  , 'name' => 'updated_by', 'orderable' => false, 'searchable' => false],
                    ['data' => 'updated_at', 'name' => 'updated_at'],
                    ['data' => 'status', 'name' => 'status', 'orderable' => false, 'searchable' => false],
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
