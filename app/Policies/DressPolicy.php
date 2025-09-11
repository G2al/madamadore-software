<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Dress;

class DressPolicy
{
    /**
     * Solo admin può vedere la lista, lo staff può solo leggerla.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'staff']);
    }

    /**
     * Tutti i ruoli possono vedere un singolo record.
     */
    public function view(User $user, Dress $dress): bool
    {
        return in_array($user->role, ['admin', 'staff']);
    }

    /**
     * Solo admin può creare.
     */
    public function create(User $user): bool
{
    // solo admin può creare
    return $user->role === 'admin';
}

public function update(User $user, Dress $dress): bool
{
    // solo admin può modificare
    return $user->role === 'admin';
}

public function delete(User $user, Dress $dress): bool
{
    return $user->role === 'admin';
}
}
