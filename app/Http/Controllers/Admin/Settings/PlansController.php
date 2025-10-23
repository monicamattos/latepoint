<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPlanSubscriptionRenewal;
use App\Models\Admin;
use App\Models\Plan;
use App\Models\PlanSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlansController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $admin = Admin::query()->where('user_id', $user->id)->first();

        if (!$admin) {
            abort(403, __('Admin profile not found.'));
        }

        $plans = Plan::query()->forUser($user)->orderBy('price')->get();
        $subscription = $admin->activePlanSubscription();
        $ownedPlans = collect();

        $isSuperAdmin = is_null($user->parent_user_id);

        if ($isSuperAdmin) {
            $ownedPlans = $plans;
        }

        return view('content.settings.plans', [
            'user' => $user,
            'admin' => $admin,
            'plans' => $plans,
            'ownedPlans' => $ownedPlans,
            'subscription' => $subscription,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Plan::class);

        $user = $request->user();
        $data = $this->validatedPlanData($request, $user);
        $data['owner_user_id'] = $user->id;

        Plan::create($data);

        return redirect()
            ->route('admin.settings-plans')
            ->with('success', __('Plan created successfully.'));
    }

    public function update(Request $request, Plan $plan)
    {
        $this->authorize('update', $plan);

        $data = $this->validatedPlanData($request, $request->user(), $plan);

        $plan->fill($data);
        $plan->save();

        return redirect()
            ->route('admin.settings-plans')
            ->with('success', __('Plan updated successfully.'));
    }

    public function destroy(Plan $plan)
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()
            ->route('admin.settings-plans')
            ->with('success', __('Plan removed successfully.'));
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($request->integer('plan_id'));

        $this->authorize('subscribe', [PlanSubscription::class, $plan]);

        $subscription = PlanSubscription::startSubscription($plan, $request->user());

        if ($subscription->renews_at) {
            ProcessPlanSubscriptionRenewal::dispatch($subscription->id)
                ->delay($subscription->renews_at);
        }

        return redirect()
            ->route('admin.settings-plans')
            ->with('success', __('Plan activated successfully.'));
    }

    public function cancel(PlanSubscription $subscription)
    {
        $this->authorize('cancel', $subscription);

        $subscription->cancel();

        return redirect()
            ->route('admin.settings-plans')
            ->with('success', __('Subscription canceled successfully.'));
    }

    public function resume(PlanSubscription $subscription)
    {
        $this->authorize('resume', $subscription);

        $subscription->resume();

        if ($subscription->renews_at) {
            ProcessPlanSubscriptionRenewal::dispatch($subscription->id)
                ->delay($subscription->renews_at);
        }

        return redirect()
            ->route('admin.settings-plans')
            ->with('success', __('Subscription resumed successfully.'));
    }

    protected function validatedPlanData(Request $request, $user, ?Plan $plan = null): array
    {
        $slugRule = Rule::unique('plans')->where(function ($query) use ($user) {
            return $query->where('owner_user_id', $user->id);
        });

        if ($plan) {
            $slugRule = $slugRule->ignore($plan->id);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                $slugRule,
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_interval' => ['required', 'in:weekly,monthly,yearly'],
            'billing_interval_count' => ['required', 'integer', 'min:1'],
            'trial_period_days' => ['nullable', 'integer', 'min:0'],
            'feature_settings' => ['sometimes', 'array'],
            'feature_settings.*' => ['nullable'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        $data = $request->validate($rules);

        $data['is_active'] = $request->has('is_active')
            ? (bool) $request->boolean('is_active')
            : ($plan?->is_active ?? true);

        if (!array_key_exists('slug', $data) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['currency'] = strtoupper($data['currency']);
        $data['trial_period_days'] = $data['trial_period_days'] ?? 0;

        if (isset($data['feature_settings'])) {
            $features = [];
            foreach ($data['feature_settings'] as $key => $value) {
                $features[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($features[$key] === null) {
                    $features[$key] = (bool) $value;
                }
            }
            $data['feature_settings'] = $features;
        }

        return $data;
    }
}
