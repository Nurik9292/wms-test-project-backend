<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\ProductUnit;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'article' => fake()->unique()->bothify('???-####'),
            'barcode' => fake()->optional()->ean13(),
            'category_id' => ProductCategory::factory(),
            'unit' => fake()->randomElement(ProductUnit::cases()),
            'purchase_price' => fake()->optional()->randomFloat(2, 10, 50000),
            'photo_url' => null,
            'status' => ProductStatus::Active,
            'type' => fake()->randomElement(ProductType::cases()),
            'track_serials' => fake()->boolean(30),
            'length_cm' => fake()->optional()->randomFloat(2, 1, 100),
            'width_cm' => fake()->optional()->randomFloat(2, 1, 100),
            'height_cm' => fake()->optional()->randomFloat(2, 1, 50),
            'weight_kg' => fake()->optional()->randomFloat(3, 0.01, 10),
            'min_stock' => fake()->optional()->numberBetween(1, 50),
            'max_stock' => fake()->optional()->numberBetween(50, 200),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => ProductStatus::Active]);
    }

    public function archived(): static
    {
        return $this->state(['status' => ProductStatus::Archive]);
    }
}
