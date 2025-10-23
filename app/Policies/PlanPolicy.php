<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;

class PlanPolicy
{
    protected function isSuperAdmin(User $user): bool
    {
        return is_null($user->parent_user_id);
    }

    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user, Plan $plan): bool
    {
        return $this->isSuperAdmin($user) && (int) $plan->owner_user_id === (int) $user->id;
    }

    public function delete(User $user, Plan $plan): bool
    {
        return $this->update($user, $plan);
    }
}
