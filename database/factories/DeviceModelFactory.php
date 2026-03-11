<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeviceModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceModelFactory extends Factory
{
    protected $model = DeviceModel::class;

    public function definition(): array
    {
        return [
            'brand' => fake()->randomElement(['Apple', 'Samsung', 'Xiaomi', 'Huawei']),
            'name' => fake()->words(2, true),
        ];
    }
}
