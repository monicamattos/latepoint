<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PlanSubscription extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_id',
        'user_id',
        'provider_user_id',
        'status',
        'starts_at',
        'trial_ends_at',
        'renews_at',
        'ends_at',
        'canceled_at',
        'last_renewed_at',
        'feature_overrides',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'renews_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'last_renewed_at' => 'datetime',
        'feature_overrides' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Subscription belongs to a plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Subscription belongs to the subscribing user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Subscription belongs to the provider (super admin).
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    /**
     * Scope for active subscriptions.
     */
    public function scopeActive($query)
    {
        $now = CarbonImmutable::now();

        return $query->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('canceled_at')->orWhere('canceled_at', '>', $now);
            });
    }

    /**
     * Cancel any active subscriptions for a user.
     */
    public static function cancelActiveForUser(int $userId): void
    {
        $now = CarbonImmutable::now();

        static::query()
            ->where('user_id', $userId)
            ->active()
            ->update([
                'status' => 'canceled',
                'canceled_at' => $now,
                'ends_at' => $now,
            ]);
    }

    /**
     * Start a new subscription for a user.
     */
    public static function startSubscription(Plan $plan, User $user, array $overrides = []): self
    {
        return DB::transaction(function () use ($plan, $user, $overrides) {
            static::cancelActiveForUser($user->id);

            $now = CarbonImmutable::now();
            $trialEndsAt = $plan->trial_period_days > 0
                ? $now->addDays($plan->trial_period_days)
                : null;

            $subscription = static::create([
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'provider_user_id' => $plan->owner_user_id ?? $user->parent_user_id,
                'status' => 'active',
                'starts_at' => $now,
                'trial_ends_at' => $trialEndsAt,
                'renews_at' => $plan->nextRenewalDate($trialEndsAt ? $trialEndsAt : $now),
                'feature_overrides' => $overrides ?: null,
            ]);

            return $subscription;
        });
    }

    /**
     * Determine if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->scopeActive(static::query()->whereKey($this->getKey()))->exists();
    }

    /**
     * Determine if subscription allows a feature.
     */
    public function allowsFeature(string $feature): bool
    {
        $overrides = $this->feature_overrides ?? [];

        if (array_key_exists($feature, $overrides)) {
            return (bool) $overrides[$feature];
        }

        return $this->plan?->allowsFeature($feature) ?? false;
    }

    /**
     * Determine whether the subscription is due for renewal.
     */
    public function isDueForRenewal(): bool
    {
        if (!$this->renews_at) {
            return false;
        }

        $now = CarbonImmutable::now();

        return $now->greaterThanOrEqualTo(CarbonImmutable::parse($this->renews_at));
    }

    /**
     * Renew the subscription by extending renewal date.
     */
    public function renew(): void
    {
        if (!$this->plan) {
            return;
        }

        $from = $this->renews_at ? CarbonImmutable::parse($this->renews_at) : CarbonImmutable::now();
        $nextRenewal = $this->plan->nextRenewalDate($from);

        $this->fill([
            'renews_at' => $nextRenewal,
            'last_renewed_at' => CarbonImmutable::now(),
            'status' => 'active',
        ]);

        $this->save();
    }

    /**
     * Mark the subscription as canceled.
     */
    public function cancel(?CarbonImmutable $canceledAt = null): void
    {
        $canceledAt = $canceledAt ?? CarbonImmutable::now();

        $this->fill([
            'status' => 'canceled',
            'canceled_at' => $canceledAt,
            'ends_at' => $canceledAt,
        ]);

        $this->save();
    }

    /**
     * Resume the subscription.
     */
    public function resume(): void
    {
        if (!$this->plan) {
            return;
        }

        $now = CarbonImmutable::now();
        $renewalSource = $this->renews_at ? CarbonImmutable::parse($this->renews_at) : $now;

        $this->fill([
            'status' => 'active',
            'canceled_at' => null,
            'ends_at' => null,
            'renews_at' => $this->plan->nextRenewalDate($renewalSource->lessThan($now) ? $now : $renewalSource),
        ]);

        $this->save();
    }

    /**
     * Return the effective feature settings.
     */
    public function featureSettings(): array
    {
        $base = $this->plan?->feature_settings ?? [];
        $overrides = $this->feature_overrides ?? [];

        return array_replace($base, $overrides);
    }
}
