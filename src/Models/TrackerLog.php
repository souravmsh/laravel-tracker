<?php

namespace Souravmsh\LaravelTracker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackerLog extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        "created_at" => "datetime",
        "updated_at" => "datetime",
        "deleted_at" => "datetime",
    ];

    public function referral()
    {
        return $this->belongsTo(TrackerReferral::class, "referral_code", "code");
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, "user_id");
    }
}
