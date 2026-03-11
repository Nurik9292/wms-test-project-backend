<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DeviceModel;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCharacteristic;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->createCategories();
        $characteristics = $this->createCharacteristics();
        $deviceModels = $this->createDeviceModels();

        $this->createDisplays($categories['displays'], $characteristics, $deviceModels);
        $this->createBatteries($categories['batteries'], $characteristics, $deviceModels);
        $this->createBoards($categories['boards'], $characteristics, $deviceModels);
        $this->createCameras($categories['cameras'], $characteristics, $deviceModels);
        $this->createCases($categories['cases'], $characteristics, $deviceModels);
        $this->createTools($categories['tools']);
        $this->createConsumables($categories['consumables']);
        $this->createDonors($categories['displays'], $deviceModels);
    }

    private function createCategories(): array
    {
        $names = [
            'displays' => 'Дисплеи',
            'batteries' => 'Аккумуляторы',
            'boards' => 'Платы',
            'cameras' => 'Камеры',
            'cases' => 'Корпуса',
            'tools' => 'Инструменты',
            'consumables' => 'Расходники',
        ];

        $categories = [];
        $order = 0;
        foreach ($names as $key => $name) {
            $categories[$key] = ProductCategory::create([
                'name' => $name,
                'sort_order' => $order++,
            ]);
        }

        return $categories;
    }

    private function createCharacteristics(): array
    {
        $items = [
            ['name' => 'Цвет', 'type' => 'string', 'unit' => null],
            ['name' => 'Размер экрана', 'type' => 'number', 'unit' => 'дюйм'],
            ['name' => 'Ёмкость батареи', 'type' => 'number', 'unit' => 'мАч'],
            ['name' => 'Совместимость', 'type' => 'string', 'unit' => null],
            ['name' => 'Разрешение', 'type' => 'string', 'unit' => 'px'],
            ['name' => 'Тип матрицы', 'type' => 'string', 'unit' => null],
        ];

        $result = [];
        foreach ($items as $item) {
            $result[$item['name']] = ProductCharacteristic::create($item);
        }

        return $result;
    }

    private function createDeviceModels(): array
    {
        $models = [
            'Apple' => ['iPhone 13', 'iPhone 13 Pro', 'iPhone 14', 'iPhone 14 Pro', 'iPhone 15', 'iPhone 15 Pro', 'iPhone 16', 'iPhone 16 Pro'],
            'Samsung' => ['Galaxy S22', 'Galaxy S22 Ultra', 'Galaxy S23', 'Galaxy S23 Ultra', 'Galaxy S24', 'Galaxy S24 Ultra'],
            'Xiaomi' => ['Xiaomi 13', 'Xiaomi 13 Pro', 'Xiaomi 14', 'Xiaomi 14 Pro'],
        ];

        $result = [];
        foreach ($models as $brand => $names) {
            foreach ($names as $name) {
                $result[] = DeviceModel::create(['brand' => $brand, 'name' => $name]);
            }
        }

        return $result;
    }

    private function createDisplays(ProductCategory $category, array $chars, array $deviceModels): void
    {
        $displays = [
            ['name' => 'Дисплей iPhone 15 Pro OLED', 'article' => 'DSP-IP15P-001', 'price' => 4500, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'OLED', 'devices' => [4, 5]],
            ['name' => 'Дисплей iPhone 15 Pro Max OLED', 'article' => 'DSP-IP15PM-001', 'price' => 5200, 'color' => 'Чёрный', 'size' => '6.7', 'matrix' => 'OLED', 'devices' => [5]],
            ['name' => 'Дисплей iPhone 14 Pro OLED', 'article' => 'DSP-IP14P-001', 'price' => 3800, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'OLED', 'devices' => [2, 3]],
            ['name' => 'Дисплей iPhone 14 LCD копия', 'article' => 'DSP-IP14-002', 'price' => 1200, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'LCD', 'devices' => [2]],
            ['name' => 'Дисплей iPhone 13 OLED', 'article' => 'DSP-IP13-001', 'price' => 2800, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'OLED', 'devices' => [0, 1]],
            ['name' => 'Дисплей Samsung S24 Ultra AMOLED', 'article' => 'DSP-SS24U-001', 'price' => 6500, 'color' => 'Чёрный', 'size' => '6.8', 'matrix' => 'AMOLED', 'devices' => [13]],
            ['name' => 'Дисплей Samsung S23 AMOLED', 'article' => 'DSP-SS23-001', 'price' => 3500, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'AMOLED', 'devices' => [10, 11]],
            ['name' => 'Дисплей Samsung S22 AMOLED', 'article' => 'DSP-SS22-001', 'price' => 2900, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'AMOLED', 'devices' => [8, 9]],
            ['name' => 'Дисплей Xiaomi 14 Pro AMOLED', 'article' => 'DSP-XM14P-001', 'price' => 3200, 'color' => 'Чёрный', 'size' => '6.73', 'matrix' => 'AMOLED', 'devices' => [17]],
            ['name' => 'Дисплей Xiaomi 13 AMOLED', 'article' => 'DSP-XM13-001', 'price' => 2400, 'color' => 'Чёрный', 'size' => '6.36', 'matrix' => 'AMOLED', 'devices' => [14, 15]],
        ];

        foreach ($displays as $d) {
            $product = Product::create([
                'name' => $d['name'],
                'article' => $d['article'],
                'barcode' => fake()->ean13(),
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => $d['price'],
                'status' => 'active',
                'type' => 'component',
                'track_serials' => true,
                'min_stock' => 3,
                'max_stock' => 15,
            ]);

            $product->characteristicValues()->createMany([
                ['characteristic_id' => $chars['Цвет']->id, 'value' => $d['color']],
                ['characteristic_id' => $chars['Размер экрана']->id, 'value' => $d['size']],
                ['characteristic_id' => $chars['Тип матрицы']->id, 'value' => $d['matrix']],
            ]);

            $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $d['devices']);
            $product->deviceModels()->attach($modelIds);
        }
    }

    private function createBatteries(ProductCategory $category, array $chars, array $deviceModels): void
    {
        $batteries = [
            ['name' => 'Аккумулятор iPhone 15 Pro', 'article' => 'BAT-IP15P-001', 'price' => 850, 'capacity' => '3274', 'devices' => [4, 5]],
            ['name' => 'Аккумулятор iPhone 14 Pro', 'article' => 'BAT-IP14P-001', 'price' => 750, 'capacity' => '3200', 'devices' => [2, 3]],
            ['name' => 'Аккумулятор iPhone 13', 'article' => 'BAT-IP13-001', 'price' => 600, 'capacity' => '3227', 'devices' => [0, 1]],
            ['name' => 'Аккумулятор Samsung S24 Ultra', 'article' => 'BAT-SS24U-001', 'price' => 1100, 'capacity' => '5000', 'devices' => [13]],
            ['name' => 'Аккумулятор Samsung S23', 'article' => 'BAT-SS23-001', 'price' => 800, 'capacity' => '3900', 'devices' => [10, 11]],
            ['name' => 'Аккумулятор Xiaomi 14', 'article' => 'BAT-XM14-001', 'price' => 650, 'capacity' => '4610', 'devices' => [16, 17]],
        ];

        foreach ($batteries as $b) {
            $product = Product::create([
                'name' => $b['name'],
                'article' => $b['article'],
                'barcode' => fake()->ean13(),
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => $b['price'],
                'status' => 'active',
                'type' => 'component',
                'track_serials' => false,
                'min_stock' => 5,
                'max_stock' => 30,
            ]);

            $product->characteristicValues()->create([
                'characteristic_id' => $chars['Ёмкость батареи']->id,
                'value' => $b['capacity'],
            ]);

            $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $b['devices']);
            $product->deviceModels()->attach($modelIds);
        }
    }

    private function createBoards(ProductCategory $category, array $chars, array $deviceModels): void
    {
        $boards = [
            ['name' => 'Плата зарядки iPhone 15', 'article' => 'BRD-IP15-CH01', 'price' => 450, 'devices' => [4, 5]],
            ['name' => 'Плата зарядки Samsung S24', 'article' => 'BRD-SS24-CH01', 'price' => 380, 'devices' => [12, 13]],
            ['name' => 'Шлейф Face ID iPhone 14 Pro', 'article' => 'BRD-IP14P-FID', 'price' => 1200, 'devices' => [2, 3]],
            ['name' => 'Шлейф кнопки питания iPhone 13', 'article' => 'BRD-IP13-PWR', 'price' => 280, 'devices' => [0, 1]],
        ];

        foreach ($boards as $b) {
            $product = Product::create([
                'name' => $b['name'],
                'article' => $b['article'],
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => $b['price'],
                'status' => 'active',
                'type' => 'component',
                'track_serials' => false,
                'min_stock' => 3,
                'max_stock' => 20,
            ]);

            $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $b['devices']);
            $product->deviceModels()->attach($modelIds);
        }
    }

    private function createCameras(ProductCategory $category, array $chars, array $deviceModels): void
    {
        $cameras = [
            ['name' => 'Основная камера iPhone 15 Pro', 'article' => 'CAM-IP15P-MAIN', 'price' => 2200, 'devices' => [4, 5]],
            ['name' => 'Фронтальная камера iPhone 14', 'article' => 'CAM-IP14-FRT', 'price' => 800, 'devices' => [2, 3]],
            ['name' => 'Основная камера Samsung S24 Ultra', 'article' => 'CAM-SS24U-MAIN', 'price' => 3100, 'devices' => [13]],
            ['name' => 'Камера Xiaomi 13 Pro', 'article' => 'CAM-XM13P-MAIN', 'price' => 1800, 'devices' => [15]],
        ];

        foreach ($cameras as $c) {
            $product = Product::create([
                'name' => $c['name'],
                'article' => $c['article'],
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => $c['price'],
                'status' => 'active',
                'type' => 'component',
                'track_serials' => true,
                'min_stock' => 2,
                'max_stock' => 10,
            ]);

            $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $c['devices']);
            $product->deviceModels()->attach($modelIds);
        }
    }

    private function createCases(ProductCategory $category, array $chars, array $deviceModels): void
    {
        $cases = [
            ['name' => 'Задняя крышка iPhone 15 Pro', 'article' => 'CAS-IP15P-BCK', 'price' => 1500, 'color' => 'Natural Titanium', 'devices' => [4, 5]],
            ['name' => 'Рамка Samsung S23 Ultra', 'article' => 'CAS-SS23U-FRM', 'price' => 900, 'color' => 'Phantom Black', 'devices' => [11]],
            ['name' => 'Задняя крышка Xiaomi 14', 'article' => 'CAS-XM14-BCK', 'price' => 600, 'color' => 'Чёрный', 'devices' => [16, 17]],
        ];

        foreach ($cases as $c) {
            $product = Product::create([
                'name' => $c['name'],
                'article' => $c['article'],
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => $c['price'],
                'status' => 'active',
                'type' => 'component',
                'track_serials' => false,
                'min_stock' => 2,
                'max_stock' => 10,
            ]);

            $product->characteristicValues()->create([
                'characteristic_id' => $chars['Цвет']->id,
                'value' => $c['color'],
            ]);

            $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $c['devices']);
            $product->deviceModels()->attach($modelIds);
        }
    }

    private function createTools(ProductCategory $category): void
    {
        $tools = [
            ['name' => 'Набор отвёрток для iPhone', 'article' => 'TLS-SCRW-IP01', 'price' => 350],
            ['name' => 'Присоска для снятия дисплеев', 'article' => 'TLS-SUCT-001', 'price' => 120],
            ['name' => 'Медиатор металлический', 'article' => 'TLS-PICK-M01', 'price' => 45],
            ['name' => 'Медиатор пластиковый (10 шт)', 'article' => 'TLS-PICK-P10', 'price' => 80],
            ['name' => 'Паяльная станция', 'article' => 'TLS-SOLDER-001', 'price' => 4500],
            ['name' => 'Микроскоп бинокулярный', 'article' => 'TLS-MICRO-001', 'price' => 12000],
            ['name' => 'Ультразвуковая ванна', 'article' => 'TLS-ULTRA-001', 'price' => 3500],
            ['name' => 'Термовоздушная станция', 'article' => 'TLS-HGUN-001', 'price' => 5800],
        ];

        foreach ($tools as $t) {
            Product::create([
                'name' => $t['name'],
                'article' => $t['article'],
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => $t['price'],
                'status' => 'active',
                'type' => 'regular',
                'track_serials' => false,
                'min_stock' => 1,
                'max_stock' => 5,
            ]);
        }
    }

    private function createConsumables(ProductCategory $category): void
    {
        $consumables = [
            ['name' => 'Клей B-7000 50мл', 'article' => 'CON-GLUE-B7K', 'price' => 180, 'unit' => 'pcs'],
            ['name' => 'Двусторонний скотч для дисплея iPhone 15', 'article' => 'CON-TAPE-IP15', 'price' => 45, 'unit' => 'pcs'],
            ['name' => 'Изопропиловый спирт 1л', 'article' => 'CON-IPA-1L', 'price' => 250, 'unit' => 'l'],
            ['name' => 'Флюс паяльный RMA-223', 'article' => 'CON-FLUX-RMA', 'price' => 320, 'unit' => 'pcs'],
            ['name' => 'Припой ПОС-61 0.5мм 100г', 'article' => 'CON-SOLDER-05', 'price' => 280, 'unit' => 'kg'],
            ['name' => 'Салфетки безворсовые (100 шт)', 'article' => 'CON-WIPE-100', 'price' => 150, 'unit' => 'pcs'],
            ['name' => 'OCA плёнка для ламинации iPhone 15', 'article' => 'CON-OCA-IP15', 'price' => 65, 'unit' => 'pcs'],
            ['name' => 'Поляризатор для iPhone 14', 'article' => 'CON-POL-IP14', 'price' => 95, 'unit' => 'pcs'],
        ];

        foreach ($consumables as $c) {
            Product::create([
                'name' => $c['name'],
                'article' => $c['article'],
                'category_id' => $category->id,
                'unit' => $c['unit'],
                'purchase_price' => $c['price'],
                'status' => 'active',
                'type' => 'regular',
                'track_serials' => false,
                'min_stock' => 5,
                'max_stock' => 50,
            ]);
        }
    }

    private function createDonors(ProductCategory $category, array $deviceModels): void
    {
        $donors = [
            ['name' => 'iPhone 13 донор (iCloud)', 'article' => 'DNR-IP13-001', 'price' => 5000, 'devices' => [0]],
            ['name' => 'iPhone 14 донор (разбит дисплей)', 'article' => 'DNR-IP14-001', 'price' => 8000, 'devices' => [2]],
            ['name' => 'Samsung S22 донор', 'article' => 'DNR-SS22-001', 'price' => 4500, 'devices' => [8]],
            ['name' => 'Xiaomi 13 донор', 'article' => 'DNR-XM13-001', 'price' => 3000, 'devices' => [14]],
            ['name' => 'iPhone 15 Pro донор (утопленник)', 'article' => 'DNR-IP15P-001', 'price' => 12000, 'devices' => [5]],
        ];

        foreach ($donors as $d) {
            $product = Product::create([
                'name' => $d['name'],
                'article' => $d['article'],
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => $d['price'],
                'status' => 'active',
                'type' => 'donor',
                'track_serials' => true,
            ]);

            $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $d['devices']);
            $product->deviceModels()->attach($modelIds);
        }
    }
}
