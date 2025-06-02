<?php

use Illuminate\Support\Facades\Route;
use Souravmsh\LaravelTracker\Http\Controllers\TrackerApiController;

Route::prefix("tracker")->group(function () {
    Route::get("/", [TrackerApiController::class, "index"]);
    Route::get("/stats", [TrackerApiController::class, "stats"]);
});
