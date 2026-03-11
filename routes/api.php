<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CharacteristicController;
use App\Http\Controllers\Api\V1\DeviceModelController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::apiResource('products', ProductController::class);
    Route::post('products/{product}/restore', [ProductController::class, 'restore'])->withTrashed();

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('characteristics', CharacteristicController::class)->except(['show']);
    Route::apiResource('device-models', DeviceModelController::class)->only(['index', 'store']);
});
