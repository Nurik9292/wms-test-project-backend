<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CharacteristicType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCharacteristic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'type' => CharacteristicType::class,
        ];
    }
}
