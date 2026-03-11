<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository
{
    public function query(): Builder
    {
        return Product::query();
    }

    public function paginate(Builder $query, int $perPage = 20): LengthAwarePaginator
    {
        return $query->paginate($perPage);
    }

    public function find(int $id, array $includes = []): Product
    {
        return Product::with($includes)->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    public function restore(int $id): Product
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return $product->fresh();
    }
}
