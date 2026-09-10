@php($user = $user ?? null)

<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" name="name" id="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $user?->name) }}" autofocus>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" name="email" id="email"
        class="form-control @error('email') is-invalid @enderror"
        value="{{ old('email', $user?->email) }}">
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="password" class="form-label">
        Password @if($user) <span class="text-body-secondary">(leave blank to keep current)</span> @endif
    </label>
    <input type="password" name="password" id="password"
        class="form-control @error('password') is-invalid @enderror">
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="client_id" class="form-label">Client</label>
        @if($lockClient ?? false)
            <input type="text" class="form-control" value="{{ $clients->first() }}" disabled>
            <input type="hidden" name="client_id" value="{{ $clients->keys()->first() }}">
        @else
            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror">
                <option value="">Internal (no client)</option>
                @foreach($clients as $id => $name)
                    <option value="{{ $id }}" @selected((int) old('client_id', $user?->client_id) === $id)>{{ $name }}</option>
                @endforeach
            </select>
        @endif
        @error('client_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="role_id" class="form-label">Role</label>
        <select name="role_id" id="role_id" class="form-select @error('role_id') is-invalid @enderror">
            <option value="">No role</option>
            @foreach($roles as $id => $name)
                <option value="{{ $id }}" @selected((int) old('role_id', $user?->role_id) === $id)>{{ $name }}</option>
            @endforeach
        </select>
        @error('role_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
