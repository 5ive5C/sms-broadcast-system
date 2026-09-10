@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            Welcome, {{ auth()->user()->name }}.
        </div>
    </div>
@stop
