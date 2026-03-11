<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class IndexProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'string', 'max:255'],
            'filter.status' => ['sometimes', 'string', 'in:active,archive'],
            'filter.type' => ['sometimes', 'string', 'in:regular,donor,component'],
            'filter.category_id' => ['sometimes', 'integer', 'exists:product_categories,id'],
            'filter.track_serials' => ['sometimes', 'string', 'in:true,false,1,0'],
            'filter.min_stock_alert' => ['sometimes', 'string', 'in:true,false,1,0'],
            'sort' => ['sometimes', 'string', 'regex:/^-?(name|article|created_at|purchase_price)$/'],
            'include' => ['sometimes', 'string'],
        ];
    }
}
