@extends('adminlte::page')

@section('title', 'New Template')

@section('content_header')
    <h1>New Template</h1>
@stop

@section('content')

    <x-adminlte-card title="New Template" icon="bi bi-file-earmark-plus">
        <form action="{{ route('templates.store') }}" method="post">
            @csrf
            @include('templates.partials.form')

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Save template
            </button>
            <a href="{{ route('templates.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </x-adminlte-card>

@stop
