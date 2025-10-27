<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'short_description',
        'parent_id',
        'selection_image_id',
        'order_number',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'category_service')
            ->withTimestamps();
    }
}
