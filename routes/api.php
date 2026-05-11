<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactController;

Route::prefix('v1')->group(function () {
    Route::apiResource('contacts', ContactController::class);
});