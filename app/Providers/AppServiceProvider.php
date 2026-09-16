<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bridges the per-role permission strings (User::hasPermission) into
        // the Gate, so features can just gate on 'campaigns.manage' etc.
        // without a dedicated Policy per permission. Scoped to the known
        // permission catalogue only — structural abilities (policy methods,
        // portal-split checks below) fall through to their own Gate/Policy
        // resolution instead of being blanket-granted by a super-admin's
        // '*' wildcard.
        Gate::before(function (User $user, string $ability) {
            if (! array_key_exists($ability, Role::PERMISSIONS)) {
                return null;
            }

            return $user->hasPermission($ability) ?: null;
        });

        // Splits the sidebar between the internal "SMS Broadcast" portal
        // and the per-tenant client portal. Super-admin sees both — an
        // internal admin can always "View as" a client to reach the
        // client-only screens too.
        Gate::define('internal-portal', fn (User $user) => $user->isInternalAdmin());
        Gate::define('viewing-as-client', fn (User $user) => $user->isInternalAdmin() && session('viewing_client_id') !== null);
    }
}
