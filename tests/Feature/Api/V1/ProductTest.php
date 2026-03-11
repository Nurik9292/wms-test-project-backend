<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\DeviceModel;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCharacteristic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products_with_pagination(): void
    {
        Product::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/products?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.pagination.total', 25)
            ->assertJsonPath('meta.pagination.per_page', 10);
    }

    public function test_can_filter_products_by_status(): void
    {
        Product::factory()->count(3)->create(['status' => ProductStatus::Active]);
        Product::factory()->count(2)->create(['status' => ProductStatus::Archive]);

        $response = $this->getJson('/api/v1/products?filter[status]=active');

        $response->assertOk()
            ->assertJsonPath('meta.pagination.total', 3);
    }

    public function test_can_filter_products_by_type(): void
    {
        Product::factory()->count(4)->create(['type' => ProductType::Component]);
        Product::factory()->count(2)->create(['type' => ProductType::Regular]);

        $response = $this->getJson('/api/v1/products?filter[type]=component');

        $response->assertOk()
            ->assertJsonPath('meta.pagination.total', 4);
    }

    public function test_can_filter_products_by_category(): void
    {
        $category = ProductCategory::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);
        Product::factory()->count(2)->create();

        $response = $this->getJson("/api/v1/products?filter[category_id]={$category->id}");

        $response->assertOk()
            ->assertJsonPath('meta.pagination.total', 3);
    }

    public function test_can_search_products_by_name_article_barcode(): void
    {
        Product::factory()->create(['name' => 'iPhone 15 Display']);
        Product::factory()->create(['article' => 'DSP-IP15-001']);
        Product::factory()->create(['barcode' => '4600000000001']);
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/products?search=iPhone');
        $response->assertOk()->assertJsonPath('meta.pagination.total', 1);

        $response = $this->getJson('/api/v1/products?search=DSP-IP15');
        $response->assertOk()->assertJsonPath('meta.pagination.total', 1);

        $response = $this->getJson('/api/v1/products?search=4600000000001');
        $response->assertOk()->assertJsonPath('meta.pagination.total', 1);
    }

    public function test_can_sort_products(): void
    {
        Product::factory()->create(['name' => 'Alpha']);
        Product::factory()->create(['name' => 'Beta']);
        Product::factory()->create(['name' => 'Gamma']);

        $response = $this->getJson('/api/v1/products?sort=name');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Alpha', $data[0]['name']);
        $this->assertEquals('Gamma', $data[2]['name']);

        $response = $this->getJson('/api/v1/products?sort=-name');
        $data = $response->json('data');
        $this->assertEquals('Gamma', $data[0]['name']);
    }

    public function test_can_create_product_with_characteristics(): void
    {
        $category = ProductCategory::factory()->create();
        $characteristic = ProductCharacteristic::factory()->create(['name' => 'Color', 'type' => 'string']);

        $payload = [
            'name' => 'Test Product',
            'article' => 'TST-001',
            'unit' => 'pcs',
            'type' => 'regular',
            'category_id' => $category->id,
            'characteristics' => [
                ['characteristic_id' => $characteristic->id, 'value' => 'Black'],
            ],
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Test Product')
            ->assertJsonPath('data.article', 'TST-001')
            ->assertJsonCount(1, 'data.characteristics');
    }

    public function test_can_create_product_with_device_models(): void
    {
        $deviceModels = DeviceModel::factory()->count(2)->create();

        $payload = [
            'name' => 'Test Display',
            'article' => 'DSP-001',
            'unit' => 'pcs',
            'type' => 'component',
            'device_model_ids' => $deviceModels->pluck('id')->toArray(),
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertCreated()
            ->assertJsonCount(2, 'data.device_models');
    }

    public function test_cannot_create_product_without_required_fields(): void
    {
        $response = $this->postJson('/api/v1/products', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'article', 'unit', 'type']);
    }

    public function test_cannot_create_product_with_duplicate_article(): void
    {
        Product::factory()->create(['article' => 'UNIQUE-001']);

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Another Product',
            'article' => 'UNIQUE-001',
            'unit' => 'pcs',
            'type' => 'regular',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['article']);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_can_archive_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_can_restore_product(): void
    {
        $product = Product::factory()->create();
        $product->delete();

        $response = $this->postJson("/api/v1/products/{$product->id}/restore");

        $response->assertOk();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    }

    public function test_can_include_relations(): void
    {
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $characteristic = ProductCharacteristic::factory()->create();
        $product->characteristicValues()->create([
            'characteristic_id' => $characteristic->id,
            'value' => 'test',
        ]);
        $deviceModel = DeviceModel::factory()->create();
        $product->deviceModels()->attach($deviceModel);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonCount(1, 'data.characteristics')
            ->assertJsonCount(1, 'data.device_models');
    }
}
