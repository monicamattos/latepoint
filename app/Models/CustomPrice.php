<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPrice extends Model
{
    use HasFactory;
    protected $fillable = [
        'agent_id',
        'service_id',
        'location_id',
        'is_price_variable',
        'price_min',
        'price_max',
        'charge_amount',
        'is_deposit_required',
        'deposit_amount',
    ];

    protected $casts = [
        'is_price_variable' => 'boolean',
        'is_deposit_required' => 'boolean',
        'price_min' => 'decimal:4',
        'price_max' => 'decimal:4',
        'charge_amount' => 'decimal:4',
        'deposit_amount' => 'decimal:4',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
