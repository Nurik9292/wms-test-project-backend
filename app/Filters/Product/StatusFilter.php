<?php

declare(strict_types=1);

namespace App\Filters\Product;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter
{
    public function __construct(
        private readonly string $status,
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        $query->where('status', $this->status);

        return $next($query);
    }
}
