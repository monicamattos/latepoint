<?php

namespace App\Models;

use Carbon\CarbonInterval;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Plan extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_interval',
        'billing_interval_count',
        'trial_period_days',
        'feature_settings',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'feature_settings' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Relationship to the plan owner (super admin).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Relationship to plan subscriptions.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(PlanSubscription::class);
    }

    /**
     * Scope plans visible to a specific user.
     */
    public function scopeForUser($query, User $user)
    {
        if (is_null($user->parent_user_id)) {
            return $query->where('owner_user_id', $user->id);
        }

        return $query->where(function ($q) use ($user) {
            $q->whereNull('owner_user_id')
              ->orWhere('owner_user_id', $user->parent_user_id);
        })->where('is_active', true);
    }

    /**
     * Determine if plan has a feature enabled.
     */
    public function allowsFeature(string $feature): bool
    {
        $settings = $this->feature_settings ?? [];

        if (array_key_exists($feature, $settings)) {
            return (bool) $settings[$feature];
        }

        return false;
    }

    /**
     * Calculate the next renewal date based on plan interval.
     */
    public function nextRenewalDate(?CarbonImmutable $from = null): CarbonImmutable
    {
        $from = $from ?? CarbonImmutable::now();
        $count = max(1, (int) $this->billing_interval_count);

        switch ($this->billing_interval) {
            case 'weekly':
                $interval = CarbonInterval::weeks($count);
                break;
            case 'yearly':
                $interval = CarbonInterval::years($count);
                break;
            case 'monthly':
            default:
                $interval = CarbonInterval::months($count);
        }

        return $from->add($interval);
    }

    /**
     * Generate a slug if it is missing.
     */
    protected static function booted(): void
    {
        static::creating(function (Plan $plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }
}
