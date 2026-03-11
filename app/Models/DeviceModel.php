<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeviceModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'name',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_device_model');
    }
}
