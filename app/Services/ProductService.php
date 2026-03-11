<?php

declare(strict_types=1);

namespace App\Services;

use App\Filters\Product\CategoryFilter;
use App\Filters\Product\MinStockAlertFilter;
use App\Filters\Product\SearchFilter;
use App\Filters\Product\StatusFilter;
use App\Filters\Product\TrackSerialsFilter;
use App\Filters\Product\TypeFilter;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $repository,
        private readonly Pipeline $pipeline,
    ) {}

    public function getProducts(array $filters = [], int $perPage = 20, ?string $sort = null, array $includes = []): LengthAwarePaginator
    {
        $query = $this->repository->query();

        if ($includes) {
            $query->with($includes);
        }

        $query = $this->pipeline
            ->send($query)
            ->through($this->getFilters($filters))
            ->thenReturn();

        $this->applySorting($query, $sort);

        return $this->repository->paginate($query, $perPage);
    }

    public function getProduct(int $id, array $includes = []): Product
    {
        return $this->repository->find($id, $includes);
    }

    public function createProduct(array $data): Product
    {
        $characteristics = $data['characteristics'] ?? [];
        $deviceModelIds = $data['device_model_ids'] ?? [];
        unset($data['characteristics'], $data['device_model_ids']);

        $product = $this->repository->create($data);

        $this->syncCharacteristics($product, $characteristics);
        $this->syncDeviceModels($product, $deviceModelIds);

        return $product->load(['characteristicValues.characteristic', 'deviceModels', 'category']);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $characteristics = $data['characteristics'] ?? null;
        $deviceModelIds = $data['device_model_ids'] ?? null;
        unset($data['characteristics'], $data['device_model_ids']);

        $product = $this->repository->update($product, $data);

        if ($characteristics !== null) {
            $this->syncCharacteristics($product, $characteristics);
        }

        if ($deviceModelIds !== null) {
            $this->syncDeviceModels($product, $deviceModelIds);
        }

        return $product->load(['characteristicValues.characteristic', 'deviceModels', 'category']);
    }

    public function archiveProduct(Product $product): void
    {
        $this->repository->delete($product);
    }

    public function restoreProduct(int $id): Product
    {
        return $this->repository->restore($id);
    }

    public function syncCharacteristics(Product $product, array $characteristics): void
    {
        $product->characteristicValues()->delete();

        foreach ($characteristics as $characteristic) {
            $product->characteristicValues()->create([
                'characteristic_id' => $characteristic['characteristic_id'],
                'value' => $characteristic['value'],
            ]);
        }
    }

    public function syncDeviceModels(Product $product, array $deviceModelIds): void
    {
        $product->deviceModels()->sync($deviceModelIds);
    }

    private function getFilters(array $filters): array
    {
        $pipes = [];

        if (isset($filters['status'])) {
            $pipes[] = new StatusFilter($filters['status']);
        }

        if (isset($filters['type'])) {
            $pipes[] = new TypeFilter($filters['type']);
        }

        if (isset($filters['category_id'])) {
            $pipes[] = new CategoryFilter((int) $filters['category_id']);
        }

        if (isset($filters['search'])) {
            $pipes[] = new SearchFilter($filters['search']);
        }

        if (isset($filters['track_serials'])) {
            $pipes[] = new TrackSerialsFilter(filter_var($filters['track_serials'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['min_stock_alert']) && filter_var($filters['min_stock_alert'], FILTER_VALIDATE_BOOLEAN)) {
            $pipes[] = new MinStockAlertFilter();
        }

        return $pipes;
    }

    private function applySorting(mixed $query, ?string $sort): void
    {
        if (!$sort) {
            $query->latest();
            return;
        }

        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        $allowedSorts = ['name', 'article', 'created_at', 'purchase_price'];

        if (in_array($column, $allowedSorts, true)) {
            $query->orderBy($column, $direction);
        }
    }
}
