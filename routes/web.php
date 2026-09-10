<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(auth()->check() ? '/home' : route('login'));
});

Route::middleware('auth')->get('/home', function () {
    return view('home');
});
