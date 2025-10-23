<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\PlanSubscription;
use App\Models\User;

class PlanSubscriptionPolicy
{
    public function view(User $user, PlanSubscription $subscription): bool
    {
        return (int) $subscription->user_id === (int) $user->id;
    }

    public function cancel(User $user, PlanSubscription $subscription): bool
    {
        return $this->view($user, $subscription);
    }

    public function resume(User $user, PlanSubscription $subscription): bool
    {
        return $this->view($user, $subscription);
    }

    public function subscribe(User $user, Plan $plan): bool
    {
        if (is_null($user->parent_user_id)) {
            return false;
        }

        if (!$plan->is_active) {
            return false;
        }

        if ($plan->owner_user_id && (int) $plan->owner_user_id !== (int) $user->parent_user_id) {
            return false;
        }

        return true;
    }
}
