<?php

namespace Souravmsh\LaravelTracker\Models;

use Illuminate\Database\Eloquent\Model;

class TrackerSetting extends Model
{
    protected $table    = 'tracker_settings';
    protected $guarded  = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
