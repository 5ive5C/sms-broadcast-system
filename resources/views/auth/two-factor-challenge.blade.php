@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@section('auth_header', __('adminlte::adminlte.login_message'))

@section('auth_body')
    <p class="text-center">
        {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application, or one of your emergency recovery codes.') }}
    </p>

    <form action="{{ route('two-factor.login.store') }}" method="post">
        @csrf

        {{-- Authentication code field --}}
        <label for="code" class="visually-hidden">{{ __('Code') }}</label>

        <div class="input-group mb-3">
            <input type="text" inputmode="numeric" name="code" id="code" autofocus autocomplete="one-time-code"
                class="form-control @error('code') is-invalid @enderror"
                placeholder="{{ __('Authentication code') }}">

            <div class="input-group-text">
                <span class="bi bi-shield-lock"></span>
            </div>

            @error('code')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        {{-- Recovery code field --}}
        <label for="recovery_code" class="visually-hidden">{{ __('Recovery Code') }}</label>

        <div class="input-group mb-3">
            <input type="text" name="recovery_code" id="recovery_code" autocomplete="one-time-code"
                class="form-control @error('recovery_code') is-invalid @enderror"
                placeholder="{{ __('Or a recovery code') }}">

            <div class="input-group-text">
                <span class="bi bi-key"></span>
            </div>

            @error('recovery_code')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn {{ config('adminlte.classes_auth_btn', 'btn-primary') }}">
                <i class="bi bi-box-arrow-in-right me-1"></i>
                {{ __('Log in') }}
            </button>
        </div>
    </form>
@stop
