<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Client::class, 'client');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $clients = Client::query()->with(['pricingTier', 'wallet']);

            return DataTables::of($clients)
                ->addColumn('pricing_tier', fn (Client $client) => $client->pricingTier?->name ?? '—')
                ->addColumn('balance', fn (Client $client) => number_format($client->wallet?->balance ?? 0))
                ->addColumn('status', fn (Client $client) => match ($client->status) {
                    'active' => '<span class="text-success fw-semibold">Active</span>',
                    'pending' => '<span class="text-info fw-semibold">Pending</span>',
                    'suspended' => '<span class="text-warning fw-semibold">Suspended</span>',
                    default => '<span class="text-danger fw-semibold">Inactive</span>',
                })
                ->editColumn('created_at', fn (Client $client) => $client->created_at?->format('d-M-Y H:i:s'))
                ->addColumn('actions', fn (Client $client) => view('clients.partials.actions', ['client' => $client])->render())
                ->rawColumns(['status', 'actions'])
                ->toJson();
        }

        return view('clients.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = DB::transaction(function () use ($request) {
            $client = Client::create([
                ...$request->safe()->only([
                    'name', 'company_reg_no', 'address', 'industry',
                    'pricing_tier_id', 'message_types', 'two_factor_required',
                    'pic_name', 'pic_phone', 'pic_email',
                ]),
                'slug' => $this->uniqueSlug($request->validated('name')),
                'status' => 'pending',
            ]);

            Wallet::create(['client_id' => $client->id]);

            $adminRole = $this->cloneClientAdminRole($client);

            User::create([
                'name' => $request->validated('admin_name'),
                'email' => $request->validated('admin_email'),
                'password' => Hash::make(Str::random(16)),
                'client_id' => $client->id,
                'role_id' => $adminRole->id,
                'created_by' => $request->user()->id,
            ]);

            return $client;
        });

        AuditLog::record($request->user(), 'Client registered', $client->name, $client);

        return redirect()->route('clients.index')->with('status', 'Client created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): View
    {
        return view('clients.edit', ['client' => $client]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->safe()->only([
            'name', 'company_reg_no', 'address', 'industry',
            'pricing_tier_id', 'message_types', 'two_factor_required',
            'pic_name', 'pic_phone', 'pic_email',
        ]));

        return redirect()->route('clients.index')->with('status', 'Client updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * The users.client_id foreign key cascade-deletes for real (bypassing
     * the User model's soft deletes) when its parent client row is deleted,
     * so a client with any user on it — including soft-deleted ones — is
     * blocked here rather than silently wiping their accounts.
     */
    public function destroy(Client $client): RedirectResponse
    {
        if ($client->users()->withTrashed()->exists()) {
            return redirect()->route('clients.index')
                ->with('error', 'Cannot delete a client that still has users. Remove them first.');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Client deleted.');
    }

    /**
     * Build a unique slug from the client name, appending a numeric suffix
     * on collision. There is no form field for it anymore, so this is the
     * only place a slug ever gets assigned.
     */
    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (Client::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Give the new client its own "Client Admin" role, seeded from the
     * global template's permission set — this is the row Screen 06's
     * "each client builds its own roles" model starts from.
     */
    protected function cloneClientAdminRole(Client $client): Role
    {
        $template = Role::query()->global()->where('slug', 'client-admin')->first();

        return Role::create([
            'client_id' => $client->id,
            'name' => 'Client Admin',
            'slug' => 'client-admin',
            'permissions' => $template?->permissions ?? ['*'],
        ]);
    }
}
