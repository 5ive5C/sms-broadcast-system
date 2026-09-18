@extends('adminlte::auth.auth-page')

@section('classes_body', 'app-bg')

@section('body')
    <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div style="width: 100%; max-width: 420px;" class="px-3">
            <div class="card border-0 shadow-sm" style="border-radius: 6px;">
                <div class="card-body p-4 p-md-5">
                    <h1 class="fw-bold mb-1" style="color: var(--text-primary); font-size: 15px;">Sign in</h1>
                    <p class="text-body-secondary mb-4">SMS Broadcast portal</p>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2">
                            @foreach ($errors->all() as $error)
                                <div class="small">{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @session('status')
                        <div class="alert alert-success py-2 small">{{ $value }}</div>
                    @endsession

                    <form method="post" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="form-control" autofocus required autocomplete="username">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password"
                                class="form-control" required autocomplete="current-password">
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                                <label for="remember" class="form-check-label">Remember me</label>
                            </div>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">Forgot password?</a>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">Sign in</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
