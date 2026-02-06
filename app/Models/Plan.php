<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'monthly_price',
        'annual_price',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
    ];

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'id_plan');
    }
}
