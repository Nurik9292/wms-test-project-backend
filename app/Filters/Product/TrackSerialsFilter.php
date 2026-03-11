<?php

declare(strict_types=1);

namespace App\Filters\Product;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class TrackSerialsFilter
{
    public function __construct(
        private readonly bool $trackSerials,
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        $query->where('track_serials', $this->trackSerials);

        return $next($query);
    }
}
