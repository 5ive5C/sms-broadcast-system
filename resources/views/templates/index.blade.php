@extends('adminlte::page')

@section('title', 'Templates')

@section('content_header')
    <h1>Message Templates</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <x-adminlte-card>
        <x-slot name="titleSlot">Templates</x-slot>

        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('templates.create') }}" class="btn btn-primary">
                New template <i class="bi bi-plus-lg"></i>
            </a>
        </div>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Placeholders</th>
                    <th>Parts</th>
                    <th>Last used</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                    <tr>
                        <td class="fw-semibold">{{ $template->name }}</td>
                        <td>
                            @forelse($template->placeholders as $placeholder)
                                <span class="placeholder-token me-1">{{ '{'.$placeholder.'}' }}</span>
                            @empty
                                —
                            @endforelse
                        </td>
                        <td>{{ $template->parts }}</td>
                        <td>{{ $template->last_used_at?->format('d M') ?? 'never' }}</td>
                        <td><a href="{{ route('templates.edit', $template) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-body-secondary py-4">No templates yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-adminlte-card>

@stop
