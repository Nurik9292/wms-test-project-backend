<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\CharacteristicType;
use App\Http\Controllers\Controller;
use App\Http\Resources\CharacteristicResource;
use App\Models\ProductCharacteristic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CharacteristicController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CharacteristicResource::collection(ProductCharacteristic::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(CharacteristicType::class)],
            'unit' => ['nullable', 'string', 'max:50'],
        ]);

        $characteristic = ProductCharacteristic::create($validated);

        return (new CharacteristicResource($characteristic))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, ProductCharacteristic $characteristic): CharacteristicResource
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::enum(CharacteristicType::class)],
            'unit' => ['nullable', 'string', 'max:50'],
        ]);

        $characteristic->update($validated);

        return new CharacteristicResource($characteristic->fresh());
    }

    public function destroy(ProductCharacteristic $characteristic): JsonResponse
    {
        $characteristic->delete();

        return response()->json(null, 204);
    }
}
