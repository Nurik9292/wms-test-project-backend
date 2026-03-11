<?php

declare(strict_types=1);

namespace App\Filters\Product;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class SearchFilter
{
    public function __construct(
        private readonly string $search,
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        $search = '%' . $this->search . '%';

        $driver = $query->getConnection()->getDriverName();
        $operator = $driver === 'pgsql' ? 'ilike' : 'like';

        $query->where(function (Builder $q) use ($search, $operator): void {
            $q->where('name', $operator, $search)
                ->orWhere('article', $operator, $search)
                ->orWhere('barcode', $operator, $search);
        });

        return $next($query);
    }
}
