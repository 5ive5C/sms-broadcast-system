<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $actor = $request->user();

        $users = User::query()->with(['role', 'client']);

        if (! $actor->isSuperAdmin()) {
            $users->where('client_id', $actor->client_id);
        }

        if ($request->ajax()) {
            return DataTables::of($users)
                ->addColumn('role', fn (User $user) => $user->role?->name ?? '—')
                ->addColumn('client', fn (User $user) => $user->client?->name ?? 'Internal')
                ->addColumn('actions', fn (User $user) => view('users.partials.actions', ['user' => $user])->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return view('users.create', $this->formOptions($request->user()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create([
            ...$request->safe()->only(['name', 'email', 'client_id', 'role_id']),
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('users.index')->with('status', 'User created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            ...$this->formOptions($request->user()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->fill($request->safe()->only(['name', 'email', 'client_id', 'role_id']));

        if ($password = $request->validated('password')) {
            $user->password = Hash::make($password);
        }

        $user->save();

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('users.index')->with('error', "Can't delete your own account.");
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }

    /**
     * The clients/roles available to the actor for the create/edit forms. A
     * client admin is locked to their own tenant and can't hand out the
     * super-admin role.
     *
     * @return array<string, mixed>
     */
    protected function formOptions(User $actor): array
    {
        if ($actor->isSuperAdmin()) {
            return [
                'clients' => Client::query()->orderBy('name')->pluck('name', 'id'),
                'roles' => Role::query()->orderBy('name')->pluck('name', 'id'),
                'lockClient' => false,
            ];
        }

        return [
            'clients' => Client::query()->whereKey($actor->client_id)->pluck('name', 'id'),
            'roles' => Role::query()->where('slug', '!=', 'super-admin')->orderBy('name')->pluck('name', 'id'),
            'lockClient' => true,
        ];
    }
}
