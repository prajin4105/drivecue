<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappTopup extends Model
{
    protected $table = 'whatsapp_topups';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'package_id',
        'reminders_added',
        'price_paid',
        'payment_status',
        'notes',
        'granted_by',
    ];

    protected $casts = [
        'reminders_added' => 'integer',
        'price_paid' => 'decimal:2',
        'granted_by' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TopupPackage::class, 'package_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
