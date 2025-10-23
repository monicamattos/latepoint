<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'short_description',
        'is_price_variable',
        'price_min',
        'price_max',
        'charge_amount',
        'deposit_amount',
        'is_deposit_required',
        'duration_name',
        'duration',
        'buffer_before',
        'buffer_after',
        'category_id',
        'order_number',
        'selection_image_id',
        'description_image_id',
        'bg_color',
        'timeblock_interval',
        'capacity_min',
        'capacity_max',
        'status',
        'visibility',
        'override_default_booking_status',
    ];

    public function customPrices()
    {
        return $this->hasMany(CustomPrice::class);
    }

    public function priceForAgent(?int $agentId, int $locationId = 0)
    {
        if (!$agentId) {
            return $this->charge_amount;
        }

        $customPrice = $this->customPrices()
            ->where('agent_id', $agentId)
            ->where('location_id', $locationId)
            ->first();

        return $customPrice?->charge_amount ?? $this->charge_amount;
    }

    public function configuration(): array
    {
        if (!$this->short_description) {
            return [];
        }

        $decoded = json_decode($this->short_description, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function offeredAgentIds(): array
    {
        $configuration = $this->configuration();

        if (!isset($configuration['offer']) || !is_array($configuration['offer'])) {
            return [];
        }

        $agentIds = [];

        foreach ($configuration['offer'] as $agentId => $value) {
            if ($this->isTruthy($value)) {
                $agentIds[] = (int) $agentId;
            }
        }

        return array_values(array_unique($agentIds));
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
