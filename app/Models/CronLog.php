<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['command', 'status', 'output', 'started_at', 'completed_at'];
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
