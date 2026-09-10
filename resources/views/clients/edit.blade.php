@extends('adminlte::page')

@section('title', 'Edit Client')

@section('content_header')
    <h1>Edit Client</h1>
@stop

@section('content')

    <x-adminlte-card title="Edit Client" theme="primary" icon="bi bi-building-gear">
        <form action="{{ route('clients.update', $client) }}" method="post">
            @csrf
            @method('PUT')
            @include('clients.partials.form', ['client' => $client])

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Save
            </button>
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </x-adminlte-card>

@stop
