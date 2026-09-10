@extends('adminlte::page')

@section('title', 'Add User')

@section('content_header')
    <h1>Add User</h1>
@stop

@section('content')

    <x-adminlte-card title="New User" theme="primary" icon="bi bi-person-plus">
        <form action="{{ route('users.store') }}" method="post">
            @csrf
            @include('users.partials.form')

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Create
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </x-adminlte-card>

@stop
