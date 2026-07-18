<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronSetting extends Model
{
    public $timestamps = false;
    protected $fillable = ['command', 'schedule', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
