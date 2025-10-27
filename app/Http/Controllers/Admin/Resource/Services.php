<?php

namespace App\Http\Controllers\Admin\Resource;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Agent;
use App\Models\CustomPrice;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceExtra;
use Illuminate\Http\Request;

class Services extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::with('categories')->get();

        $categories = ServiceCategory::all();

        return view('content.resource.services', compact('services', 'categories'));
    }


    public function get()
    {
        $services = Service::with('categories')->get();

        $categories = ServiceCategory::pluck('name', 'id');

        $groupedServices = [];

        foreach ($services as $service) {
            $categoryIds = $service->categories
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (empty($categoryIds)) {
                $groupedServices[0][] = $service;
                continue;
            }

            foreach ($categoryIds as $categoryId) {
                $groupedServices[$categoryId][] = $service;
            }
        }

        $response = collect($groupedServices)->map(function ($services, $categoryId) use ($categories) {
            $categoryName = $categoryId == 0
                ? __('Uncategorized')
                : ($categories[$categoryId] ?? __('Unknown Category'));

            $servicesData = collect($services)->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'duration' => $service->duration,
                    'buffer_before' => $service->buffer_before,
                    'buffer_after' => $service->buffer_after,
                    'capacity_min' => $service->capacity_min,
                    'capacity_max' => $service->capacity_max,
                    'category_ids' => $service->categories->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                ];
            });

            return [
                'category' => $categoryName,
                'services' => $servicesData,
            ];
        })->values();

        return response()->json($response);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('content.resource.createservices');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255', // Adjust length if needed
            'short_description' => 'string|nullable',
            'price_min' => 'nullable|numeric|min:0.0000', // Allow decimals, non-negative
            'price_max' => 'nullable|numeric|min:0.0000', // Non-negative, greater than price_min
            'charge_amount' => 'nullable|numeric|min:0.0000',  // Allow decimals, non-negative
            'deposit_amount' => 'nullable|numeric|min:0.0000', // Allow decimals, non-negative
            'duration_name' => 'string|nullable|max:255', // Adjust length if needed
            'duration' => 'required|string', // Required duration format
            'buffer_before' => 'string|nullable', // Validate time format if needed
            'buffer_after' => 'string|nullable', // Validate time format if needed
            'category_id' => 'string|nullable', // Backwards compatibility
            'category_ids' => 'array|nullable',
            'category_ids.*' => 'integer|exists:service_categories,id',
            'bg_color' => 'string|nullable', // Validate hex color format (optional)
            'timeblock_interval' => 'string|nullable', // Validate time interval format
            'capacity_min' => 'nullable|numeric|min:0', // Non-negative integer
            'capacity_max' => 'nullable|numeric|min:0', // Non-negative integer, greater than capacity_min
            'status' => 'required|string', // Validate allowed statuses
            'visibility' => 'required|string', // Validate allowed visibility options
            'override_default_booking_status' => 'string|nullable|max:255', // Adjust length if needed

        ]);

        $categoryIds = $this->resolveCategoryIds($request);

        $service = new Service;
        $service->name = $validatedData['name'];
        $service->short_description = $validatedData['short_description'];
        $service->price_min = $validatedData['price_min'];
        $service->price_max = $validatedData['price_max'];
        $service->charge_amount = $validatedData['charge_amount'];
        $service->deposit_amount = $validatedData['deposit_amount'];
        $service->duration_name = $validatedData['duration_name'];
        $service->duration = $validatedData['duration'];
        $service->buffer_before = $validatedData['buffer_before'];
        $service->buffer_after = $validatedData['buffer_after'];
        $service->category_id = $this->primaryCategoryId($categoryIds);
        $service->selection_image_id = $request->selection_image_id;
        $service->description_image_id = $request->description_image_id;
        $service->bg_color = $validatedData['bg_color'];
        $service->timeblock_interval = $validatedData['timeblock_interval'];
        $service->capacity_min = $validatedData['capacity_min'];
        $service->capacity_max = $validatedData['capacity_max'];
        $service->status = $validatedData['status'];
        $service->visibility = $validatedData['visibility'];
        $service->override_default_booking_status = $validatedData['override_default_booking_status'];

        $service->save();

        $service->categories()->sync($categoryIds);

        $this->syncCustomPrices($service, $request);

        $activity = new Activity();
        $activity->service_id = $service->id;
        $activity->code = "service_created";
        $activity->description = json_encode([
            'service_data' => [
                'name' => $validatedData['name'],
                'short_description' => $validatedData['short_description'],
                'price_min' => $validatedData['price_min'],
                'price_max' => $validatedData['price_max'],
                'charge_amount' => $validatedData['charge_amount'],
                'deposit_amount' => $validatedData['deposit_amount'],
                'duration_name' => $validatedData['duration_name'],
                'duration' => $validatedData['duration'],
                'buffer_before' => $validatedData['buffer_before'],
                'buffer_after' => $validatedData['buffer_after'],
                'primary_category_id' => $service->category_id,
                'category_ids' => $categoryIds,
                'bg_color' => $validatedData['bg_color'],
                'timeblock_interval' => $validatedData['timeblock_interval'],
                'capacity_min' => $validatedData['capacity_min'],
                'capacity_max' => $validatedData['capacity_max'],
                'status' => $validatedData['status'],
                'visibility' => $validatedData['visibility'],
                'override_default_booking_status' => $validatedData['override_default_booking_status'],
            ]
        ]);
        $activity->initiated_by = "admin";
        $activity->initiated_by_id = $request->user()['id'];
        $activity->save();

        if ($request->ajax()) {
            return response()->json([
                'message' => __('Service created successfully.'),
                'id' => $service->id,
            ], 201);
        }

        return redirect('/admin/resource/services')->with('success', __('Service created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $service = Service::with(['customPrices', 'categories'])->findOrFail($id);
        $categories = ServiceCategory::all();
        $agents = Agent::all();
        $extras = ServiceExtra::all();

        $customPricesByAgent = $service->customPrices
            ->where('location_id', 0)
            ->mapWithKeys(function ($price) {
                return [$price->agent_id => $price->charge_amount];
            })
            ->toArray();

        return view('content.resource.editservices', compact('service', 'categories', 'agents', 'extras', 'customPricesByAgent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255', // Adjust length if needed
            'price_min' => 'nullable|numeric|min:0.0000', // Allow decimals, non-negative
            'price_max' => 'nullable|numeric|min:0.0000', // Non-negative, greater than price_min
            'charge_amount' => 'nullable|numeric|min:0.0000',  // Allow decimals, non-negative
            'deposit_amount' => 'nullable|numeric|min:0.0000', // Allow decimals, non-negative
            'duration_name' => 'string|nullable|max:255', // Adjust length if needed
            'duration' => 'required|string', // Required duration format
            'buffer_before' => 'string|nullable', // Validate time format if needed
            'buffer_after' => 'string|nullable', // Validate time format if needed
            'category_id' => 'string|nullable',
            'category_ids' => 'array|nullable',
            'category_ids.*' => 'integer|exists:service_categories,id',
            'bg_color' => 'string|nullable', // Validate hex color format (optional)
            'timeblock_interval' => 'string|nullable', // Validate time interval format
            'capacity_min' => 'nullable|numeric|min:0', // Non-negative integer
            'capacity_max' => 'nullable|numeric|min:0', // Non-negative integer, greater than capacity_min
            'status' => 'required|string', // Validate allowed statuses
            'visibility' => 'required|string', // Validate allowed visibility options
            'override_default_booking_status' => 'string|nullable|max:255', // Adjust length if needed

        ]);
        $service = Service::with('categories')->findOrFail($request->id);

        $categoryIds = $this->resolveCategoryIds($request);

        $service->name = $validatedData['name'];
        $service->short_description = $request->short_description;
        $service->price_min = $validatedData['price_min'];
        $service->price_max = $validatedData['price_max'];
        $service->charge_amount = $validatedData['charge_amount'];
        $service->deposit_amount = $validatedData['deposit_amount'];
        $service->duration_name = $validatedData['duration_name'];
        $service->duration = $validatedData['duration'];
        $service->buffer_before = $validatedData['buffer_before'];
        $service->buffer_after = $validatedData['buffer_after'];
        $service->category_id = $this->primaryCategoryId($categoryIds);
        $service->bg_color = $validatedData['bg_color'];
        $service->timeblock_interval = $validatedData['timeblock_interval'];
        $service->capacity_min = $validatedData['capacity_min'];
        $service->capacity_max = $validatedData['capacity_max'];
        $service->status = $validatedData['status'];
        $service->visibility = $validatedData['visibility'];
        $service->override_default_booking_status = $validatedData['override_default_booking_status'];


        if ($request->selection_image_id) {
            $service->selection_image_id = $request->selection_image_id;
        }
        if ($request->description_image_id) {
            $service->description_image_id = $request->description_image_id;
        }

        $service->save();

        $service->categories()->sync($categoryIds);

        $this->syncCustomPrices($service, $request);

        $activity = new Activity();
        $activity->service_id = $service->id;
        $activity->code = "service_updated";
        $activity->description = json_encode([
            'service_data' => [
                'name' => $validatedData['name'],
                'price_min' => $validatedData['price_min'],
                'price_max' => $validatedData['price_max'],
                'charge_amount' => $validatedData['charge_amount'],
                'deposit_amount' => $validatedData['deposit_amount'],
                'duration_name' => $validatedData['duration_name'],
                'duration' => $validatedData['duration'],
                'buffer_before' => $validatedData['buffer_before'],
                'buffer_after' => $validatedData['buffer_after'],
                'primary_category_id' => $service->category_id,
                'category_ids' => $categoryIds,
                'bg_color' => $validatedData['bg_color'],
                'timeblock_interval' => $validatedData['timeblock_interval'],
                'capacity_min' => $validatedData['capacity_min'],
                'capacity_max' => $validatedData['capacity_max'],
                'status' => $validatedData['status'],
                'visibility' => $validatedData['visibility'],
                'override_default_booking_status' => $validatedData['override_default_booking_status'],
            ]
        ]);
        $activity->initiated_by = "admin";
        $activity->initiated_by_id = $request->user()['id'];
        $activity->save();

        if ($request->ajax()) {
            return response()->json([
                'message' => __('Service updated successfully.'),
                'id' => $service->id,
            ]);
        }

        return redirect('/admin/resource/services')->with('success', __('Service updated successfully.'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $service = Service::with('categories')->findOrFail($id);

        $activity = new Activity();
        $activity->service_id = $service->id;
        $activity->code = "service_deleted";
        $activity->description = json_encode([
            'service_data' => [
                'name' => $service->name,
                'short_description' => $service->short_description,
                'price_min' => $service->price_min,
                'price_max' => $service->price_max,
                'charge_amount' => $service->charge_amount,
                'deposit_amount' => $service->deposit_amount,
                'duration_name' => $service->duration_name,
                'duration' => $service->duration,
                'buffer_before' => $service->buffer_before,
                'buffer_after' => $service->buffer_after,
                'primary_category_id' => $service->category_id,
                'category_ids' => $service->categories->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'bg_color' => $service->bg_color,
                'timeblock_interval' => $service->timeblock_interval,
                'capacity_min' => $service->capacity_min,
                'capacity_max' => $service->capacity_max,
                'status' => $service->status,
                'visibility' => $service->visibility,
                'override_default_booking_status' => $service->override_default_booking_status,
            ]
        ]);
        $activity->initiated_by = "admin";
        // $activity->initiated_by_id = $request->user()['id'];
        $activity->save();

        CustomPrice::where('service_id', $service->id)->delete();
        $service->categories()->detach();
        $service->delete();

        return redirect('/admin/resource/services')->with('success', __('Service deleted successfully.'));
    }

    protected function resolveCategoryIds(Request $request): array
    {
        $raw = $request->input('category_ids', $request->input('category_id'));

        if ($raw === null || $raw === '' || $raw === []) {
            return [];
        }

        $categoryIds = is_array($raw) ? $raw : [$raw];

        $categoryIds = array_filter(array_map(static function ($value) {
            if ($value === null || $value === '' || $value === '0') {
                return null;
            }

            return (int) $value;
        }, $categoryIds));

        return array_values(array_unique($categoryIds));
    }

    protected function primaryCategoryId(array $categoryIds): ?string
    {
        if (empty($categoryIds)) {
            return null;
        }

        $first = reset($categoryIds);

        return $first !== false ? (string) $first : null;
    }

    protected function syncCustomPrices(Service $service, Request $request): void
    {
        $customPrices = $this->parseCustomPriceInput($request->input('custom_prices', []));
        $activeAgentIds = $this->extractActiveAgentIds($request->input('short_description', $service->short_description));

        if (empty($activeAgentIds)) {
            CustomPrice::where('service_id', $service->id)
                ->where('location_id', 0)
                ->delete();

            return;
        }

        if (empty($customPrices)) {
            CustomPrice::where('service_id', $service->id)
                ->where('location_id', 0)
                ->delete();

            return;
        }

        $customPrices = array_intersect_key($customPrices, array_flip($activeAgentIds));

        if (empty($customPrices)) {
            CustomPrice::where('service_id', $service->id)
                ->where('location_id', 0)
                ->delete();

            return;
        }

        CustomPrice::where('service_id', $service->id)
            ->where('location_id', 0)
            ->whereNotIn('agent_id', array_keys($customPrices))
            ->delete();

        foreach ($customPrices as $agentId => $amount) {
            CustomPrice::updateOrCreate(
                [
                    'service_id' => $service->id,
                    'agent_id' => $agentId,
                    'location_id' => 0,
                ],
                [
                    'charge_amount' => $amount,
                    'is_price_variable' => false,
                    'price_min' => null,
                    'price_max' => null,
                    'is_deposit_required' => $service->is_deposit_required,
                    'deposit_amount' => $service->deposit_amount,
                ]
            );
        }
    }

    protected function parseCustomPriceInput($input): array
    {
        if (is_string($input)) {
            $decoded = json_decode($input, true);
            $input = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($input)) {
            return [];
        }

        $normalized = [];

        foreach ($input as $agentId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_string($value)) {
                $value = preg_replace('/[^0-9,.-]/', '', $value);

                if ($value === '' || $value === '-' || $value === '.' || $value === ',') {
                    continue;
                }

                if (str_contains($value, ',') && !str_contains($value, '.')) {
                    $value = str_replace(',', '.', $value);
                } else {
                    $value = str_replace(',', '', $value);
                }
            }

            if (!is_numeric($value)) {
                continue;
            }

            $numericValue = (float) $value;

            if ($numericValue < 0) {
                continue;
            }

            $normalized[(int) $agentId] = number_format($numericValue, 4, '.', '');
        }

        return $normalized;
    }

    protected function extractActiveAgentIds(?string $shortDescription): array
    {
        if (!$shortDescription) {
            return [];
        }

        $decoded = json_decode($shortDescription, true);

        if (!is_array($decoded) || !isset($decoded['offer']) || !is_array($decoded['offer'])) {
            return [];
        }

        $activeAgentIds = [];

        foreach ($decoded['offer'] as $agentId => $value) {
            if ($this->isTruthy($value)) {
                $activeAgentIds[] = (int) $agentId;
            }
        }

        return array_values(array_unique($activeAgentIds));
    }

    protected function isTruthy($value): bool
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
}
