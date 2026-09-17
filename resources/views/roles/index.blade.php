@extends('adminlte::page')

@section('title', 'Staff and Roles')

@section('content_header')
    <h1>Staff and Roles</h1>
@stop

@section('content')

    @include('partials.sweetalert')

    <p class="text-body-secondary">Each client builds its own roles. Permissions are per client, not a fixed set.</p>

    <div class="row">
        @foreach($roles as $role)
            <div class="col-lg-6 mb-4">
                <x-adminlte-card>
                    <x-slot name="titleSlot">
                        {{ $role->name }}
                        <span class="badge text-bg-secondary ms-1">{{ $role->users_count }} user(s)</span>
                        @if(is_null($role->client_id))
                            <span class="badge text-bg-light text-dark ms-1">Global template</span>
                        @endif
                    </x-slot>

                    <form action="{{ route('roles.update', $role) }}" method="post">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Role name</label>
                            <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                        </div>

                        <div class="mb-3">
                            @foreach($catalogue as $slug => $label)
                                <div class="form-check">
                                    <input type="checkbox" name="permissions[]" value="{{ $slug }}"
                                        id="perm-{{ $role->id }}-{{ $slug }}" class="form-check-input"
                                        @checked($role->hasPermission($slug))>
                                    <label for="perm-{{ $role->id }}-{{ $slug }}" class="form-check-label">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-outline-primary btn-sm">Save</button>
                    </form>
                </x-adminlte-card>
            </div>
        @endforeach

        <div class="col-lg-6 mb-4">
            <x-adminlte-card>
                <x-slot name="titleSlot">New Role</x-slot>

                <form action="{{ route('roles.store') }}" method="post">
                    @csrf

                    <div class="mb-3">
                        <label for="new_role_name" class="form-label">Role name</label>
                        <input type="text" name="name" id="new_role_name"
                            class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        @foreach($catalogue as $slug => $label)
                            <div class="form-check">
                                <input type="checkbox" name="permissions[]" value="{{ $slug }}"
                                    id="perm-new-{{ $slug }}" class="form-check-input"
                                    @checked(in_array($slug, old('permissions', [])))>
                                <label for="perm-new-{{ $slug }}" class="form-check-label">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">Create role</button>
                </form>
            </x-adminlte-card>
        </div>
    </div>

@stop
