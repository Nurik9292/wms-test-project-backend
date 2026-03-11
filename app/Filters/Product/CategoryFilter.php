<?php

declare(strict_types=1);

namespace App\Filters\Product;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class CategoryFilter
{
    public function __construct(
        private readonly int $categoryId,
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        $query->where('category_id', $this->categoryId);

        return $next($query);
    }
}
