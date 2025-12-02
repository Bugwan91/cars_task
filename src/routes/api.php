<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CarController;

Route::name('api.')->group(function () {
	Route::apiResource('cars', CarController::class);
});
