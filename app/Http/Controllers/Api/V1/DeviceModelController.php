<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeviceModelResource;
use App\Models\DeviceModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeviceModelController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DeviceModelResource::collection(
            DeviceModel::orderBy('brand')->orderBy('name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $deviceModel = DeviceModel::create($validated);

        return (new DeviceModelResource($deviceModel))
            ->response()
            ->setStatusCode(201);
    }
}
