<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CharacteristicController;
use App\Http\Controllers\Api\V1\DeviceModelController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\Supplier\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {

    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {

        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::put('auth/me', [AuthController::class, 'updateProfile']);

        Route::middleware('tenant')->group(function (): void {

            Route::middleware('permission:products.view')->group(function (): void {
                Route::get('products', [ProductController::class, 'index']);
                Route::get('products/{product}', [ProductController::class, 'show']);
            });
            Route::post('products', [ProductController::class, 'store'])->middleware('permission:products.create');
            Route::put('products/{product}', [ProductController::class, 'update'])->middleware('permission:products.edit');
            Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('permission:products.delete');
            Route::post('products/{product}/restore', [ProductController::class, 'restore'])->withTrashed()->middleware('permission:products.delete');

            Route::apiResource('categories', CategoryController::class);
            Route::apiResource('characteristics', CharacteristicController::class)->except(['show']);
            Route::apiResource('device-models', DeviceModelController::class)->only(['index', 'store']);

            Route::get('roles', fn () => response()->json([
                'data' => \Spatie\Permission\Models\Role::where('guard_name', 'web')
                    ->whereNot('name', 'supplier')
                    ->get(['id', 'name']),
            ]));

            Route::middleware('permission:employees.manage')->group(function (): void {
                Route::get('employees', [EmployeeController::class, 'index']);
                Route::post('employees', [EmployeeController::class, 'store']);
                Route::get('employees/{employee}', [EmployeeController::class, 'show']);
                Route::put('employees/{employee}', [EmployeeController::class, 'update']);
                Route::delete('employees/{employee}', [EmployeeController::class, 'destroy']);
                Route::post('employees/{employee}/restore', [EmployeeController::class, 'restore']);
            });

            Route::middleware('permission:suppliers.manage')->group(function (): void {
                Route::get('suppliers', [SupplierController::class, 'index']);
                Route::post('suppliers', [SupplierController::class, 'store']);
                Route::put('suppliers/{supplier}', [SupplierController::class, 'update']);
                Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy']);
            });
        });

        Route::middleware('permission:supplier.orders')->prefix('supplier')->group(function (): void {
            Route::get('orders', [OrderController::class, 'index']);
            Route::get('orders/{order}', [OrderController::class, 'show']);
        });
    });
});
