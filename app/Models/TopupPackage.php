<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TopupPackage extends Model
{
    protected $table = 'topup_packages';

    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'reminders_count',
        'price',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'reminders_count' => 'integer',
        'price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function topups(): HasMany
    {
        return $this->hasMany(WhatsappTopup::class, 'package_id');
    }
}
