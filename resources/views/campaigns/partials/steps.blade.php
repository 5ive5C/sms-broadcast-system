@php($current = $current ?? 1)

<ul class="nav nav-pills mb-4">
    @foreach(['Compose', 'Recipients', 'Merge fields', 'Review'] as $i => $label)
        <li class="nav-item">
            <span class="nav-link {{ ($i + 1) === $current ? 'active' : ($i + 1 < $current ? 'text-success' : 'disabled text-body-secondary') }}">
                {{ $i + 1 }}. {{ $label }}
            </span>
        </li>
    @endforeach
</ul>
