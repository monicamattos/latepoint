<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\StepSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'service_id' => ['required', Rule::exists('services', 'id')],
            'agent_id' => ['nullable', 'string'],
            'location_id' => ['nullable', Rule::exists('locations', 'id')],
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'timezone' => ['nullable', 'timezone'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $service = Service::with('customPrices')->findOrFail($validated['service_id']);
        $customSettings = $this->customSettings();
        $timezone = $validated['timezone'] ?? config('app.timezone', 'UTC');

        $start = Carbon::createFromFormat('Y-m-d H:i', $validated['start_date'] . ' ' . $validated['start_time'], $timezone);
        $agentId = $this->resolveAgentId(
            $service,
            $validated['agent_id'] ?? null,
            $customSettings,
            $start,
            isset($validated['location_id']) ? (int) $validated['location_id'] : 0
        );

        $durationMinutes = max(0, $this->convertToMinutes($service->duration));
        $bufferBefore = max(0, $this->convertToMinutes($service->buffer_before));
        $bufferAfter = max(0, $this->convertToMinutes($service->buffer_after));

        $end = (clone $start)->addMinutes($durationMinutes + $bufferAfter);
        $locationId = isset($validated['location_id']) ? (int) $validated['location_id'] : null;

        $price = $service->priceForAgent($agentId, $locationId ?? 0);

        DB::transaction(function () use ($validated, $service, $agentId, $locationId, $start, $end, $durationMinutes, $bufferBefore, $bufferAfter, $price) {
            $user = User::firstOrNew(['email' => $validated['email']]);

            if (!$user->exists) {
                $user->first_name = $validated['first_name'];
                $user->last_name = $validated['last_name'] ?? '';
                $user->password = Hash::make(Str::random(16));
                $user->status = 1;
                $user->is_verified = 1;
                $user->account_type = 2;
            } else {
                $user->first_name = $validated['first_name'];
                $user->last_name = $validated['last_name'] ?? $user->last_name;

                if (!$user->is_verified) {
                    $user->is_verified = 1;
                }

                if (!$user->status) {
                    $user->status = 1;
                }

                if (!$user->account_type) {
                    $user->account_type = 2;
                }
            }

            $user->save();

            $customer = Customer::firstOrNew(['user_id' => $user->id]);
            $customer->first_name = $validated['first_name'];
            $customer->last_name = $validated['last_name'] ?? '';
            $customer->email = $validated['email'];
            $customer->phone = $validated['phone'] ?? $customer->phone;
            $customer->status = $customer->status ?: 'Active';
            $customer->save();

            Booking::create([
                'booking_code' => strtoupper(Str::random(8)),
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'start_time' => (int) $start->format('Hi'),
                'end_time' => (int) $end->format('Hi'),
                'start_datetime_utc' => $start->copy()->setTimezone('UTC'),
                'end_datetime_utc' => $end->copy()->setTimezone('UTC'),
                'buffer_before' => $bufferBefore,
                'buffer_after' => $bufferAfter,
                'duration' => $durationMinutes,
                'subtotal' => $price,
                'price' => $price,
                'status' => $service->override_default_booking_status ?: 'pending',
                'payment_status' => 'not_paid',
                'customer_id' => $user->id,
                'service_id' => $service->id,
                'agent_id' => $agentId,
                'location_id' => $locationId,
                'total_attendies' => 1,
                'coupon_code' => null,
                'coupon_discount' => null,
                'customer_comment' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('landing')->with('booking_success', 'Seu agendamento foi enviado com sucesso!');
    }

    protected function customSettings(): array
    {
        $setting = StepSetting::query()->where('label', 'bookingformsetting')->first();
        $decoded = $setting && $setting->value ? @unserialize($setting->value) : [];

        return [
            'allowAnyAgent' => $this->toBool($decoded['allow_any_agent'] ?? null),
            'anyAgentOrder' => $decoded['any_agent_order'] ?? 'random',
        ];
    }

    protected function resolveAgentId(Service $service, ?string $agentInput, array $customSettings, Carbon $start, int $locationId): int
    {
        if ($agentInput && $agentInput !== 'any') {
            $agentId = (int) $agentInput;

            if (!Agent::whereKey($agentId)->exists()) {
                throw ValidationException::withMessages([
                    'agent_id' => __('Selecione um profissional válido.'),
                ]);
            }

            $offered = $service->offeredAgentIds();

            if (!empty($offered) && !in_array($agentId, $offered, true)) {
                throw ValidationException::withMessages([
                    'agent_id' => __('O profissional escolhido não atende este serviço.'),
                ]);
            }

            return $agentId;
        }

        if (empty($customSettings['allowAnyAgent'])) {
            throw ValidationException::withMessages([
                'agent_id' => __('Selecione um profissional disponível.'),
            ]);
        }

        $eligibleIds = $service->offeredAgentIds();

        if (empty($eligibleIds)) {
            $eligibleIds = Agent::pluck('id')->all();
        }

        if (empty($eligibleIds)) {
            throw ValidationException::withMessages([
                'agent_id' => __('Nenhum profissional está disponível no momento.'),
            ]);
        }

        $order = $customSettings['anyAgentOrder'] ?? 'random';

        return match ($order) {
            'price_high' => $this->agentByPrice($service, $eligibleIds, 'desc', $locationId),
            'price_low' => $this->agentByPrice($service, $eligibleIds, 'asc', $locationId),
            'busy_high' => $this->agentByLoad($eligibleIds, $start, 'desc'),
            'busy_low' => $this->agentByLoad($eligibleIds, $start, 'asc'),
            default => (int) $eligibleIds[array_rand($eligibleIds)],
        };
    }

    protected function agentByPrice(Service $service, array $agentIds, string $direction, int $locationId): int
    {
        $prices = [];

        foreach ($agentIds as $id) {
            $prices[(int) $id] = (float) ($service->priceForAgent((int) $id, $locationId) ?? 0);
        }

        if ($direction === 'asc') {
            asort($prices);
        } else {
            arsort($prices);
        }

        return (int) array_key_first($prices);
    }

    protected function agentByLoad(array $agentIds, Carbon $start, string $direction): int
    {
        $counts = Booking::query()
            ->selectRaw('agent_id, COUNT(*) as total')
            ->whereIn('agent_id', $agentIds)
            ->whereDate('start_date', $start->toDateString())
            ->groupBy('agent_id')
            ->pluck('total', 'agent_id')
            ->toArray();

        foreach ($agentIds as $id) {
            if (!array_key_exists($id, $counts)) {
                $counts[$id] = 0;
            }
        }

        if ($direction === 'asc') {
            asort($counts);
        } else {
            arsort($counts);
        }

        return (int) array_key_first($counts);
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
}
