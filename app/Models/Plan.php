<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $table = 'plans';

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_trial',
        'is_popular',
        'sort_order',
        'monthly_price',
        'yearly_price',
        'customer_limit',
        'whatsapp_limit',
        'status',
    ];

    protected $casts = [
        'is_trial' => 'boolean',
        'is_popular' => 'boolean',
        'sort_order' => 'integer',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'customer_limit' => 'integer',
        'whatsapp_limit' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
