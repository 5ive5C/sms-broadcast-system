<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\PricingTier;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            $clients = Client::query()->with('pricingTier');

            return DataTables::of($clients)
                ->addColumn('pricing_tier', fn (Client $client) => $client->pricingTier?->name ?? '—')
                ->addColumn('status', fn (Client $client) => match ($client->status) {
                    'active' => '<span class="text-success fw-semibold">Active</span>',
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
        return view('clients.create', [
            'pricingTiers' => PricingTier::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::create([
            ...$request->safe()->only(['name', 'sender_id', 'status', 'pricing_tier_id']),
            'slug' => $request->validated('slug') ?: Str::slug($request->validated('name')),
        ]);

        Wallet::create(['client_id' => $client->id]);

        return redirect()->route('clients.index')->with('status', 'Client created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): View
    {
        return view('clients.edit', [
            'client' => $client,
            'pricingTiers' => PricingTier::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update([
            ...$request->safe()->only(['name', 'sender_id', 'status', 'pricing_tier_id']),
            'slug' => $request->validated('slug') ?: Str::slug($request->validated('name')),
        ]);

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
}
