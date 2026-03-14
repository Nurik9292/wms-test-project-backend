<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductUnit;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'name',
        'article',
        'barcode',
        'category_id',
        'unit',
        'purchase_price',
        'photo_url',
        'status',
        'type',
        'track_serials',
        'length_cm',
        'width_cm',
        'height_cm',
        'weight_kg',
        'min_stock',
        'max_stock',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'type' => ProductType::class,
            'unit' => ProductUnit::class,
            'track_serials' => 'boolean',
            'purchase_price' => 'decimal:2',
            'length_cm' => 'decimal:2',
            'width_cm' => 'decimal:2',
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:3',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function characteristicValues(): HasMany
    {
        return $this->hasMany(ProductCharacteristicValue::class);
    }

    public function deviceModels(): BelongsToMany
    {
        return $this->belongsToMany(DeviceModel::class, 'product_device_model');
    }
}
