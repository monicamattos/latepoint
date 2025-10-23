<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Location;
use App\Models\LocationCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\StepSetting;
use Illuminate\Support\Facades\Auth;

class Home extends Controller
{
    public function index()
    {
        $stepLabels = [
            'select_location',
            'select_service',
            'select_service_extra',
            'select_agent',
            'select_date_time',
            'enter_information',
            'select_payment_method',
            'verify_order_details',
            'confirmation',
            'bookingformsetting',
        ];

        $settings = StepSetting::query()
            ->whereIn('label', $stepLabels)
            ->get()
            ->keyBy('label');

        $steps = [];

        foreach ($stepLabels as $label) {
            if ($label === 'bookingformsetting') {
                continue;
            }

            $steps[$label] = $this->decodeSetting($settings->get($label));
        }

        $customSettingsRaw = $this->decodeSetting($settings->get('bookingformsetting'));
        $customSettings = [
            'showTimezoneSelector' => $this->toBool($customSettingsRaw['steps_show_timezone_selector'] ?? null),
            'showTimezoneInfo' => $this->toBool($customSettingsRaw['steps_show_timezone_info'] ?? null),
            'showAgentBio' => $this->toBool($customSettingsRaw['steps_show_agent_bio'] ?? null),
            'hideAgentInfo' => $this->toBool($customSettingsRaw['steps_hide_agent_info'] ?? null),
            'allowAnyAgent' => $this->toBool($customSettingsRaw['allow_any_agent'] ?? null),
            'anyAgentOrder' => $customSettingsRaw['any_agent_order'] ?? 'random',
            'showServiceCategories' => $this->toBool($customSettingsRaw['steps_show_service_categories'] ?? null),
            'skipVerifyStep' => $this->toBool($customSettingsRaw['steps_skip_verify_step'] ?? null),
            'showLocationCategories' => $this->toBool($customSettingsRaw['steps_show_location_categories'] ?? null),
            'showDurationInMinutes' => $this->toBool($customSettingsRaw['steps_show_duration_in_minutes'] ?? null),
        ];

        $locations = Location::query()
            ->orderByRaw('order_number IS NULL')
            ->orderBy('order_number')
            ->orderBy('name')
            ->get();

        $locationCategories = LocationCategory::query()
            ->orderBy('order_number')
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $services = Service::query()
            ->with(['customPrices' => function ($query) {
                $query->where('location_id', 0);
            }])
            ->orderByRaw('order_number IS NULL')
            ->orderBy('order_number')
            ->orderBy('name')
            ->get();

        $serviceCategories = ServiceCategory::query()
            ->orderBy('order_number')
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $agents = Agent::query()
            ->orderByRaw('display_name IS NULL')
            ->orderBy('display_name')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $servicePayload = $services->map(function (Service $service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'category_id' => $service->category_id ? (int) $service->category_id : 0,
                'duration_minutes' => $this->convertToMinutes($service->duration),
                'buffer_before_minutes' => $this->convertToMinutes($service->buffer_before),
                'buffer_after_minutes' => $this->convertToMinutes($service->buffer_after),
                'base_price' => $service->charge_amount !== null ? (float) $service->charge_amount : null,
                'agent_prices' => $service->customPrices
                    ->mapWithKeys(function ($price) {
                        return [$price->agent_id => (float) $price->charge_amount];
                    })
                    ->toArray(),
                'offered_agent_ids' => $service->offeredAgentIds(),
            ];
        })->values()->toArray();

        $agentsPayload = $agents->map(function (Agent $agent) {
            $name = $agent->display_name ?: trim(($agent->first_name ?? '') . ' ' . ($agent->last_name ?? ''));

            return [
                'id' => $agent->id,
                'name' => $name !== '' ? $name : 'Profissional',
                'title' => $agent->title,
                'bio' => $agent->bio,
                'status' => $agent->status,
            ];
        })->values()->toArray();

        $servicesByCategory = $services->groupBy(function (Service $service) {
            return $service->category_id ? (int) $service->category_id : 0;
        });

        $locationsByCategory = $locations->groupBy(function (Location $location) {
            return $location->category_id ? (int) $location->category_id : 0;
        });

        $serviceCategoryNames = $serviceCategories->map(function ($category) {
            return $category->name;
        })->toArray();

        $locationCategoryNames = $locationCategories->map(function ($category) {
            return $category->name;
        })->toArray();

        $defaultTimezone = config('app.timezone', 'UTC');

        return view('landing', [
            'steps' => $steps,
            'customSettings' => $customSettings,
            'customSettingsRaw' => $customSettingsRaw,
            'locations' => $locations,
            'locationsByCategory' => $locationsByCategory,
            'locationCategories' => $locationCategories,
            'locationCategoryNames' => $locationCategoryNames,
            'services' => $services,
            'servicesByCategory' => $servicesByCategory,
            'serviceCategories' => $serviceCategories,
            'serviceCategoryNames' => $serviceCategoryNames,
            'agents' => $agents,
            'servicePayload' => $servicePayload,
            'agentsPayload' => $agentsPayload,
            'loginDestination' => $this->resolveLoginDestination(),
            'loginLabel' => Auth::check() ? 'Ir para dashboard' : 'Login',
            'timezoneOptions' => \DateTimeZone::listIdentifiers(),
            'defaultTimezone' => $defaultTimezone,
        ]);
    }

    protected function decodeSetting(?StepSetting $setting): array
    {
        if (!$setting || !$setting->value) {
            return [];
        }

        $decoded = @unserialize($setting->value);

        return is_array($decoded) ? $decoded : [];
    }

    protected function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    protected function convertToMinutes($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $matches)) {
            return ((int) $matches[1]) * 60 + (int) $matches[2];
        }

        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $value, $matches)) {
            return ((int) $matches[1]) * 60 + (int) $matches[2] + ((int) $matches[3] >= 30 ? 1 : 0);
        }

        return 0;
    }

    protected function resolveLoginDestination(): string
    {
        $user = Auth::user();

        if (!$user) {
            return route('login');
        }

        if ((int) $user->account_type === 1) {
            return route('agent.dashboard');
        }

        return route('admin.dashboard');
    }
}
