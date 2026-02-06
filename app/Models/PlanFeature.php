<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanFeature extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id_plan',
        'name',
        'description',
        'value',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'id_plan');
    }
}
