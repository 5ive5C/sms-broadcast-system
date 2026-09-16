<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInternalAdmin
{
    /**
     * Gate the "SMS Broadcast" internal admin portal off from the client
     * portal — only the internal super-admin gets past here.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return $next($request);
    }
}
