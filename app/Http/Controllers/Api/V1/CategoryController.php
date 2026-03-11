<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = ProductCategory::whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'sort_order' => ['sometimes', 'integer'],
        ]);

        $category = ProductCategory::create($validated);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ProductCategory $category): CategoryResource
    {
        $category->load('children');

        return new CategoryResource($category);
    }

    public function update(Request $request, ProductCategory $category): CategoryResource
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'sort_order' => ['sometimes', 'integer'],
        ]);

        $category->update($validated);

        return new CategoryResource($category->fresh());
    }

    public function destroy(ProductCategory $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with existing products.',
            ], 422);
        }

        $category->delete();

        return response()->json(null, 204);
    }
}
