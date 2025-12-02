<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarController;

Route::name('api.')->group(function () {
	Route::apiResource('cars', CarController::class);
});
