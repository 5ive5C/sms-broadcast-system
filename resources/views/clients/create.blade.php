@extends('adminlte::page')

@section('title', 'Add Client')

@section('content_header')
    <h1>Add Client</h1>
@stop

@section('content')

    <x-adminlte-card title="New Client" theme="primary" icon="bi bi-building-add">
        <form action="{{ route('clients.store') }}" method="post">
            @csrf
            @include('clients.partials.form')

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Create
            </button>
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </x-adminlte-card>

@stop
