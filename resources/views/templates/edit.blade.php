@extends('adminlte::page')

@section('title', 'Edit Template')

@section('content_header')
    <h1>Edit Template</h1>
@stop

@section('content')

    <x-adminlte-card title="Edit Template" icon="bi bi-file-earmark-text">
        <form action="{{ route('templates.update', $template) }}" method="post">
            @csrf
            @method('PUT')
            @include('templates.partials.form', ['template' => $template])

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i> Save template
            </button>
            <a href="{{ route('templates.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </x-adminlte-card>

@stop
