<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientPortal
{
    /**
     * Screens here assume a tenant (wallet, campaigns, quick send, ...). A
     * client user always has one; an internal admin only has one once
     * they've picked a client via the "View as" switcher — until then,
     * send them to pick one rather than let a controller hit a null
     * $user->actingClient().
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isInternalAdmin() && ! session('viewing_client_id')) {
            return redirect()->route('clients.index')
                ->with('error', 'Pick "View as" on a client first to open the client portal.');
        }

        return $next($request);
    }
}
