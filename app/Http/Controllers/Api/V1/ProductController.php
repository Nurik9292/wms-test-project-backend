<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function index(IndexProductRequest $request): ProductCollection
    {
        $filters = $request->input('filter', []);

        if ($request->has('search')) {
            $filters['search'] = $request->input('search');
        }

        $includes = $this->parseIncludes($request->input('include', ''));

        $products = $this->productService->getProducts(
            filters: $filters,
            perPage: $request->integer('per_page', 20),
            sort: $request->input('sort'),
            includes: $includes,
        );

        return new ProductCollection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        $product->load(['category', 'characteristicValues.characteristic', 'deviceModels']);

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product = $this->productService->updateProduct($product, $request->validated());

        return new ProductResource($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->archiveProduct($product);

        return response()->json(null, 204);
    }

    public function restore(Product $product): ProductResource
    {
        $product = $this->productService->restoreProduct($product->id);

        return new ProductResource($product);
    }

    private function parseIncludes(string $includeString): array
    {
        if (empty($includeString)) {
            return [];
        }

        $allowedIncludes = [
            'category' => 'category',
            'characteristics' => 'characteristicValues.characteristic',
            'deviceModels' => 'deviceModels',
        ];

        $requested = explode(',', $includeString);
        $includes = [];

        foreach ($requested as $include) {
            $include = trim($include);
            if (isset($allowedIncludes[$include])) {
                $includes[] = $allowedIncludes[$include];
            }
        }

        return $includes;
    }
}
