<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, \Closure $next) {
            Gate::authorize('users.manage');

            return $next($request);
        });
    }

    /**
     * Each client builds its own roles (Screen 06) — this lists the
     * client-scoped roles plus the global templates every client starts
     * from, and every role's permission checklist for reference.
     */
    public function index(Request $request): View
    {
        $actor = $request->user();

        $roles = Role::query()
            ->where('slug', '!=', 'super-admin')
            ->where(fn ($q) => $actor->isSuperAdmin()
                ? $q
                : $q->whereNull('client_id')->orWhere('client_id', $actor->client_id))
            ->withCount('users')
            ->orderBy('client_id')
            ->orderBy('name')
            ->get();

        return view('roles.index', [
            'roles' => $roles,
            'catalogue' => Role::PERMISSIONS,
        ]);
    }

    /**
     * Create a new custom role for the actor's own client.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $actor = $request->user();

        Role::create([
            'client_id' => $actor->isSuperAdmin() ? null : $actor->client_id,
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'permissions' => $request->validated('permissions') ?? [],
        ]);

        return redirect()->route('roles.index')->with('status', 'Role created.');
    }

    /**
     * Update a role's permission checklist.
     */
    public function update(StoreRoleRequest $request, Role $role): RedirectResponse
    {
        $actor = $request->user();

        if (! $actor->isSuperAdmin() && $role->client_id !== $actor->client_id) {
            abort(403);
        }

        $role->update([
            'name' => $request->validated('name'),
            'permissions' => $request->validated('permissions') ?? [],
        ]);

        return redirect()->route('roles.index')->with('status', 'Role updated.');
    }
}
