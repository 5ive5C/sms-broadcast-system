@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <h1>Edit User</h1>
@stop

@section('content')

    <x-adminlte-card title="Edit User" theme="primary" icon="bi bi-person-gear">
        <form action="{{ route('users.update', $user) }}" method="post">
            @csrf
            @method('PUT')
            @include('users.partials.form', ['user' => $user])

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Save
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </x-adminlte-card>

@stop
