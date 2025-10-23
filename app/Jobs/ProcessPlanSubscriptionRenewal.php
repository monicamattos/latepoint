<?php

namespace App\Jobs;

use App\Models\PlanSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPlanSubscriptionRenewal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $subscriptionId, public bool $force = false)
    {
    }

    public function handle(): void
    {
        $subscription = PlanSubscription::with('plan')->find($this->subscriptionId);

        if (!$subscription || !$subscription->isActive()) {
            return;
        }

        if (!$this->force && !$subscription->isDueForRenewal()) {
            return;
        }

        $subscription->renew();

        if ($subscription->renews_at) {
            static::dispatch($subscription->id)
                ->delay($subscription->renews_at);
        }
    }
}
