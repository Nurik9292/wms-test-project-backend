<?php

declare(strict_types=1);

namespace App\Filters\Product;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class MinStockAlertFilter
{
    public function handle(Builder $query, Closure $next): Builder
    {
        $query->whereNotNull('min_stock');

        return $next($query);
    }
}
