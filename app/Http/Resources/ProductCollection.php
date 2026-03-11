<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    public $collects = ProductResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'meta' => [
                'pagination' => [
                    'total' => $paginated['total'],
                    'per_page' => $paginated['per_page'],
                    'current_page' => $paginated['current_page'],
                    'last_page' => $paginated['last_page'],
                    'from' => $paginated['from'],
                    'to' => $paginated['to'],
                ],
            ],
        ];
    }
}
