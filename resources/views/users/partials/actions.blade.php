@php($canManage = $canManage ?? true)
@php($isSelf = $user->id === auth()->id())

<div class="d-flex align-items-center gap-2">
    @if($canManage)
        <a href="{{ route('users.edit', $user) }}" class="text-primary" title="Edit">
            <i class="bi bi-pencil-square fs-5"></i>
        </a>
    @endif

    @if($canManage && ! $isSelf)
        <form action="{{ route('users.toggle-status', $user) }}" method="post" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-link p-0 border-0 text-dark" title="{{ $user->is_active ? 'Suspend' : 'Activate' }}">
                <i class="bi {{ $user->is_active ? 'bi-lock-fill' : 'bi-unlock-fill' }} fs-5"></i>
            </button>
        </form>

        <form action="{{ route('users.destroy', $user) }}" method="post" class="d-inline"
            data-confirm="Delete {{ $user->name }}? Their record is kept but they lose access.">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-link p-0 border-0 text-danger" title="Delete">
                <i class="bi bi-trash fs-5"></i>
            </button>
        </form>
    @endif
</div>
