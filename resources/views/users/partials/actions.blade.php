<div class="d-flex gap-1">
    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary" title="Edit">
        <i class="bi bi-pencil"></i>
    </a>

    <form action="{{ route('users.destroy', $user) }}" method="post"
        onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</div>
