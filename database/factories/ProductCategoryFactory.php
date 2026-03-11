<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'parent_id' => null,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
