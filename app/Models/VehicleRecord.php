<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleRecord extends Model
{
    protected $table = 'vehicle_records';

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_mobile',
        'vehicle_number',
        'vehicle_type',
        'fuel_type',
        'puc_certificate_number',
        'issue_date',
        'expiry_date',
        'puc_price',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'puc_price' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }
}
