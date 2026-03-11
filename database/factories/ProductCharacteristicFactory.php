<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CharacteristicType;
use App\Models\ProductCharacteristic;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCharacteristicFactory extends Factory
{
    protected $model = ProductCharacteristic::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'type' => fake()->randomElement(CharacteristicType::cases()),
            'unit' => fake()->optional()->word(),
        ];
    }
}
