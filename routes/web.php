<?php

use Illuminate\Support\Facades\Route;
use Souravmsh\LaravelTracker\Http\Controllers\TrackerWebController;

Route::prefix("tracker")
    ->name("tracker.")
    ->group(function () {
        Route::get("dashboard", [TrackerWebController::class, "dashboard"])->name("dashboard");
        Route::get("visitors", [TrackerWebController::class, "visitors"])->name("visitors");
        Route::get("referrals", [TrackerWebController::class, "referrals"])->name("referrals");
        Route::post("referrals/store", [TrackerWebController::class, "saveReferral"])->name("referrals.store");
    });
