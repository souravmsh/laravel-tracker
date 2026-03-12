<?php

namespace Souravmsh\LaravelTracker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class TrackerReferral extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = [
        "status" => "boolean",
        "expires_at" => "datetime",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    public function logs()
    {
        return $this->hasMany(TrackerLog::class, "referral_code", "code");
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, "created_by");
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, "updated_by");
    }

    public static function dropdown()
    {
        return self::query()
            ->where("status", true)
            ->where(function ($query) {
                $query
                    ->whereNull("expires_at")
                    ->orWhere("expires_at", ">", now());
            })
            ->orderBy("position", "asc")
            ->pluck("title", "id")
            ->toArray();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($referral) {
            $referral->position = self::max("position") + 1;
            $referral->code =
                $referral->code ?? \Illuminate\Support\Str::random(10);
        });
    }
}
