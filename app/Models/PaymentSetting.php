<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = ['razorpay_mode', 'updated_by'];

    public static function mode(): string
    {
        return static::query()->value('razorpay_mode') === 'live' ? 'live' : 'test';
    }
}
