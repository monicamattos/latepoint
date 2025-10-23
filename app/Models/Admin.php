<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'display_name',
        'email',
        'phone',
        'status',
    ];

    /**
     * Relationship to the underlying user model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All plan subscriptions associated with the admin (via the user id).
     */
    public function planSubscriptions(): HasMany
    {
        return $this->hasMany(PlanSubscription::class, 'user_id', 'user_id');
    }

    /**
     * Retrieve the currently active subscription.
     */
    public function activePlanSubscription(): ?PlanSubscription
    {
        return $this->planSubscriptions()
            ->active()
            ->orderByDesc('starts_at')
            ->first();
    }

    /**
     * Retrieve the active plan instance.
     */
    public function activePlan(): ?Plan
    {
        return $this->activePlanSubscription()?->plan;
    }

    /**
     * Determine if the admin has access to a feature.
     */
    public function hasFeature(string $feature): bool
    {
        $subscription = $this->activePlanSubscription();

        if (!$subscription || !$subscription->allowsFeature($feature)) {
            return false;
        }

        $allowedRoles = $this->featureRolesRestriction($feature);

        if ($allowedRoles === null || $allowedRoles === []) {
            return true;
        }

        return in_array($this->status, $allowedRoles, true);
    }

    /**
     * Retrieve feature role restrictions defined in settings.
     */
    protected function featureRolesRestriction(string $feature): ?array
    {
        $setting = Setting::query()
            ->where('name', 'plan_feature_roles')
            ->value('value');

        if (!$setting) {
            return null;
        }

        $decoded = json_decode($setting, true);

        if (!is_array($decoded) || !array_key_exists($feature, $decoded)) {
            return null;
        }

        $roles = $decoded[$feature];

        if (is_array($roles)) {
            return array_filter(array_map('strval', $roles));
        }

        if (is_string($roles) && $roles !== '') {
            return array_map('trim', explode(',', $roles));
        }

        return [];
    }
}
