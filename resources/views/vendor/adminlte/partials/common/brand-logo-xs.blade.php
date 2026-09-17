@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php
    $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home');
    $dashboard_url = $layoutHelper->makeUrl($dashboard_url);

    $authUser = auth()->user();
    $logoText = $authUser && ! $authUser->isInternalAdmin() && $authUser->client
        ? e($authUser->client->name)
        : 'SMS Broadcast';
    $brandClass = config('adminlte.classes_brand', '');
@endphp

@if($layoutHelper->isLayoutTopnavEnabled())

    {{-- Navbar Brand (topnav layout) --}}
    <a href="{{ $dashboard_url }}" class="navbar-brand d-flex align-items-center {{ $brandClass }}">
        <span class="fw-bold">{{ $logoText }}</span>
    </a>

@else

    {{-- Sidebar Brand --}}
    <div class="sidebar-brand">
        <a href="{{ $dashboard_url }}" class="brand-link {{ $brandClass }}">
            <span class="brand-text fw-bold">{{ $logoText }}</span>
        </a>
    </div>

@endif
