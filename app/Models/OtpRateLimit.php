<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpRateLimit extends Model
{
    protected $table = 'otp_rate_limit';

    public $timestamps = false; // We use created_at manually

    protected $fillable = [
        'identifier',
        'type', // phone or ip
    ];
}
