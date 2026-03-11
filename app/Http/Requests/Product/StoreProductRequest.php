<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'article' => ['required', 'string', 'max:100', 'unique:products,article'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'unit' => ['required', Rule::enum(ProductUnit::class)],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'photo_url' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', Rule::enum(ProductStatus::class)],
            'type' => ['required', Rule::enum(ProductType::class)],
            'track_serials' => ['sometimes', 'boolean'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'height_cm' => ['nullable', 'numeric', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'max_stock' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'characteristics' => ['sometimes', 'array'],
            'characteristics.*.characteristic_id' => ['required_with:characteristics', 'integer', 'exists:product_characteristics,id'],
            'characteristics.*.value' => ['required_with:characteristics', 'string', 'max:255'],
            'device_model_ids' => ['sometimes', 'array'],
            'device_model_ids.*' => ['integer', 'exists:device_models,id'],
        ];
    }
}
