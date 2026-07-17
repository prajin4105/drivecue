<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderLog extends Model
{
    protected $table = 'reminder_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'vehicle_record_id',
        'customer_mobile',
        'message_type',
        'reminder_stage',
        'message_body',
        'status',
        'provider_response',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicleRecord(): BelongsTo
    {
        return $this->belongsTo(VehicleRecord::class, 'vehicle_record_id');
    }
}
