<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Clients are the tenant root entity — only the internal super-admin
     * onboards, edits or removes them. A client admin manages their own
     * client's users/settings, never the client record itself.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Client $client): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Client $client): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->isSuperAdmin();
    }
}
