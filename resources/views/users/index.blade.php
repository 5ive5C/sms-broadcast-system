@extends('adminlte::page')

@section('title', 'Users')

@section('content_header')
    <h1>Users</h1>
@stop

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

    <x-adminlte-card title="All Users" theme="primary" icon="bi bi-people">
        <x-slot name="toolsSlot">
            <a href="{{ route('users.create') }}" class="btn btn-sm btn-light">
                <i class="bi bi-plus-lg"></i> Add User
            </a>
        </x-slot>

        <x-adminlte-datatable id="users-table" :heads="['Name', 'Email', 'Role', 'Client', ['label' => 'Actions', 'no-export' => true]]"
            striped hoverable with-buttons
            :config="[
                'processing' => true,
                'serverSide' => true,
                'ajax' => route('users.index'),
                'lengthMenu' => [[10, 25, 50, -1], [10, 25, 50, 'All']],
                'columns' => [
                    ['data' => 'name', 'name' => 'name'],
                    ['data' => 'email', 'name' => 'email'],
                    ['data' => 'role', 'name' => 'role.name'],
                    ['data' => 'client', 'name' => 'client.name'],
                    ['data' => 'actions', 'name' => 'actions', 'orderable' => false, 'searchable' => false],
                ],
                'buttons' => [
                    ['extend' => 'csv', 'className' => 'btn-secondary', 'text' => '<i class=\'bi bi-filetype-csv text-primary\'></i> CSV', 'exportOptions' => ['columns' => ':not([dt-no-export])']],
                    ['extend' => 'excel', 'className' => 'btn-secondary', 'text' => '<i class=\'bi bi-file-earmark-excel text-success\'></i> Excel', 'exportOptions' => ['columns' => ':not([dt-no-export])']],
                    ['extend' => 'pdf', 'className' => 'btn-secondary', 'text' => '<i class=\'bi bi-file-earmark-pdf text-danger\'></i> PDF', 'exportOptions' => ['columns' => ':not([dt-no-export])']],
                ],
            ]" />
    </x-adminlte-card>

@stop
