<?php

declare(strict_types=1);

namespace App\Filters\Product;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class TypeFilter
{
    public function __construct(
        private readonly string $type,
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        $query->where('type', $this->type);

        return $next($query);
    }
}
