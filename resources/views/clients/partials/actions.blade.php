<div class="d-flex align-items-center gap-2">
    <a href="{{ route('clients.edit', $client) }}" class="text-primary" title="Edit">
        <i class="bi bi-pencil-square fs-5"></i>
    </a>

    <form action="{{ route('clients.destroy', $client) }}" method="post" class="d-inline"
        onsubmit="return confirm('Delete {{ addslashes($client->name) }}? This is only possible while it has no users.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-link p-0 border-0 text-danger" title="Delete">
            <i class="bi bi-trash fs-5"></i>
        </button>
    </form>
</div>
