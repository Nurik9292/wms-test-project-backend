<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DeviceModel;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCharacteristic;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $characteristics = $this->createCharacteristics();
        $deviceModels = $this->createDeviceModels();

        $tenant1 = $this->createTenant1($characteristics, $deviceModels);
        $tenant2 = $this->createTenant2($characteristics, $deviceModels);

        $this->createSharedSupplier($tenant1, $tenant2);
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

    private function createTenant1(array $chars, array $deviceModels): Tenant
    {
        $tenant = Tenant::create([
            'name' => 'РемТех Сервис',
            'slug' => 'remtech-service',
            'is_active' => true,
        ]);

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Админ РемТех',
            'email' => 'admin@remtech.ru',
            'phone' => '+79001111111',
            'password' => 'password',
        ]);
        $admin->assignRole('admin');
        $tenant->update(['owner_id' => $admin->id]);

        $users = [
            ['name' => 'Мастер РемТех', 'email' => 'master@remtech.ru', 'role' => 'master'],
            ['name' => 'Старший мастер РемТех', 'email' => 'senior@remtech.ru', 'role' => 'senior_master'],
            ['name' => 'Кладовщик РемТех', 'email' => 'warehouse@remtech.ru', 'role' => 'warehouse'],
            ['name' => 'Закупщик РемТех', 'email' => 'purchaser@remtech.ru', 'role' => 'purchaser'],
        ];

        foreach ($users as $u) {
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => 'password',
            ]);
            $user->assignRole($u['role']);
        }

        $this->createIPhoneProducts($tenant, $chars, $deviceModels);

        return $tenant;
    }

    private function createTenant2(array $chars, array $deviceModels): Tenant
    {
        $tenant = Tenant::create([
            'name' => 'ФиксМобайл',
            'slug' => 'fixmobile',
            'is_active' => true,
        ]);

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Админ ФиксМобайл',
            'email' => 'admin@fixmobile.ru',
            'phone' => '+79002222222',
            'password' => 'password',
        ]);
        $admin->assignRole('admin');
        $tenant->update(['owner_id' => $admin->id]);

        $master = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Мастер ФиксМобайл',
            'email' => 'master@fixmobile.ru',
            'password' => 'password',
        ]);
        $master->assignRole('master');

        $this->createSamsungProducts($tenant, $chars, $deviceModels);

        return $tenant;
    }

    private function createSharedSupplier(Tenant $tenant1, Tenant $tenant2): void
    {
        $user = User::create([
            'tenant_id' => null,
            'name' => 'China Parts Co.',
            'email' => 'supplier@china-parts.com',
            'password' => 'password',
        ]);
        $user->assignRole('supplier');

        $supplier = Supplier::create([
            'user_id' => $user->id,
            'company_name' => 'China Parts Co.',
            'contact_name' => 'Li Wei',
            'country' => 'China',
        ]);

        $supplier->tenants()->attach([
            $tenant1->id => ['is_active' => true],
            $tenant2->id => ['is_active' => true],
        ]);
    }

    private function createIPhoneProducts(Tenant $tenant, array $chars, array $deviceModels): void
    {
        $categories = [];
        foreach (['Дисплеи', 'Аккумуляторы', 'Платы', 'Камеры', 'Корпуса', 'Инструменты', 'Расходники'] as $i => $name) {
            $categories[$name] = ProductCategory::create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'sort_order' => $i,
            ]);
        }

        $displays = [
            ['name' => 'Дисплей iPhone 15 Pro OLED', 'article' => 'DSP-IP15P-001', 'price' => 4500, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'OLED', 'devices' => [4, 5]],
            ['name' => 'Дисплей iPhone 15 Pro Max OLED', 'article' => 'DSP-IP15PM-001', 'price' => 5200, 'color' => 'Чёрный', 'size' => '6.7', 'matrix' => 'OLED', 'devices' => [5]],
            ['name' => 'Дисплей iPhone 14 Pro OLED', 'article' => 'DSP-IP14P-001', 'price' => 3800, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'OLED', 'devices' => [2, 3]],
            ['name' => 'Дисплей iPhone 14 LCD копия', 'article' => 'DSP-IP14-002', 'price' => 1200, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'LCD', 'devices' => [2]],
            ['name' => 'Дисплей iPhone 13 OLED', 'article' => 'DSP-IP13-001', 'price' => 2800, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'OLED', 'devices' => [0, 1]],
            ['name' => 'Дисплей iPhone 16 Pro OLED', 'article' => 'DSP-IP16P-001', 'price' => 6000, 'color' => 'Чёрный', 'size' => '6.3', 'matrix' => 'OLED', 'devices' => [6, 7]],
        ];

        foreach ($displays as $d) {
            $product = Product::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $d['name'],
                'article' => $d['article'],
                'barcode' => fake()->ean13(),
                'category_id' => $categories['Дисплеи']->id,
                'unit' => 'pcs',
                'purchase_price' => $d['price'],
                'status' => 'active',
                'type' => 'component',
                'track_serials' => true,
                'min_stock' => 3,
                'max_stock' => 15,
            ]);

            $product->characteristicValues()->createMany([
                ['tenant_id' => $tenant->id, 'characteristic_id' => $chars['Цвет']->id, 'value' => $d['color']],
                ['tenant_id' => $tenant->id, 'characteristic_id' => $chars['Размер экрана']->id, 'value' => $d['size']],
                ['tenant_id' => $tenant->id, 'characteristic_id' => $chars['Тип матрицы']->id, 'value' => $d['matrix']],
            ]);

            $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $d['devices']);
            $product->deviceModels()->attach($modelIds);
        }

        $batteries = [
            ['name' => 'Аккумулятор iPhone 15 Pro', 'article' => 'BAT-IP15P-001', 'price' => 850, 'capacity' => '3274', 'devices' => [4, 5]],
            ['name' => 'Аккумулятор iPhone 14 Pro', 'article' => 'BAT-IP14P-001', 'price' => 750, 'capacity' => '3200', 'devices' => [2, 3]],
            ['name' => 'Аккумулятор iPhone 13', 'article' => 'BAT-IP13-001', 'price' => 600, 'capacity' => '3227', 'devices' => [0, 1]],
            ['name' => 'Аккумулятор iPhone 16 Pro', 'article' => 'BAT-IP16P-001', 'price' => 950, 'capacity' => '3582', 'devices' => [6, 7]],
        ];

        foreach ($batteries as $b) {
            $product = Product::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $b['name'],
                'article' => $b['article'],
                'barcode' => fake()->ean13(),
                'category_id' => $categories['Аккумуляторы']->id,
                'unit' => 'pcs',
                'purchase_price' => $b['price'],
                'status' => 'active',
                'type' => 'component',
                'track_serials' => false,
                'min_stock' => 5,
                'max_stock' => 30,
            ]);

            $product->characteristicValues()->create([
                'tenant_id' => $tenant->id,
                'characteristic_id' => $chars['Ёмкость батареи']->id,
                'value' => $b['capacity'],
            ]);

            $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $b['devices']);
            $product->deviceModels()->attach($modelIds);
        }

        $others = [
            ['name' => 'Плата зарядки iPhone 15', 'article' => 'BRD-IP15-CH01', 'price' => 450, 'cat' => 'Платы', 'devices' => [4, 5]],
            ['name' => 'Шлейф Face ID iPhone 14 Pro', 'article' => 'BRD-IP14P-FID', 'price' => 1200, 'cat' => 'Платы', 'devices' => [2, 3]],
            ['name' => 'Основная камера iPhone 15 Pro', 'article' => 'CAM-IP15P-MAIN', 'price' => 2200, 'cat' => 'Камеры', 'devices' => [4, 5]],
            ['name' => 'Фронтальная камера iPhone 14', 'article' => 'CAM-IP14-FRT', 'price' => 800, 'cat' => 'Камеры', 'devices' => [2, 3]],
            ['name' => 'Задняя крышка iPhone 15 Pro', 'article' => 'CAS-IP15P-BCK', 'price' => 1500, 'cat' => 'Корпуса', 'devices' => [4, 5]],
            ['name' => 'Набор отвёрток для iPhone', 'article' => 'TLS-SCRW-IP01', 'price' => 350, 'cat' => 'Инструменты', 'devices' => []],
            ['name' => 'Присоска для снятия дисплеев', 'article' => 'TLS-SUCT-001', 'price' => 120, 'cat' => 'Инструменты', 'devices' => []],
            ['name' => 'Медиатор металлический', 'article' => 'TLS-PICK-M01', 'price' => 45, 'cat' => 'Инструменты', 'devices' => []],
            ['name' => 'Паяльная станция', 'article' => 'TLS-SOLDER-001', 'price' => 4500, 'cat' => 'Инструменты', 'devices' => []],
            ['name' => 'Клей B-7000 50мл', 'article' => 'CON-GLUE-B7K', 'price' => 180, 'cat' => 'Расходники', 'devices' => []],
            ['name' => 'Изопропиловый спирт 1л', 'article' => 'CON-IPA-1L', 'price' => 250, 'cat' => 'Расходники', 'devices' => []],
            ['name' => 'Флюс паяльный RMA-223', 'article' => 'CON-FLUX-RMA', 'price' => 320, 'cat' => 'Расходники', 'devices' => []],
            ['name' => 'iPhone 13 донор (iCloud)', 'article' => 'DNR-IP13-001', 'price' => 5000, 'cat' => 'Дисплеи', 'devices' => [0], 'type' => 'donor', 'serials' => true],
            ['name' => 'iPhone 14 донор (разбит дисплей)', 'article' => 'DNR-IP14-001', 'price' => 8000, 'cat' => 'Дисплеи', 'devices' => [2], 'type' => 'donor', 'serials' => true],
            ['name' => 'iPhone 15 Pro донор (утопленник)', 'article' => 'DNR-IP15P-001', 'price' => 12000, 'cat' => 'Дисплеи', 'devices' => [5], 'type' => 'donor', 'serials' => true],
            ['name' => 'OCA плёнка iPhone 15', 'article' => 'CON-OCA-IP15', 'price' => 65, 'cat' => 'Расходники', 'devices' => []],
            ['name' => 'Поляризатор iPhone 14', 'article' => 'CON-POL-IP14', 'price' => 95, 'cat' => 'Расходники', 'devices' => []],
            ['name' => 'Микроскоп бинокулярный', 'article' => 'TLS-MICRO-001', 'price' => 12000, 'cat' => 'Инструменты', 'devices' => []],
            ['name' => 'Ультразвуковая ванна', 'article' => 'TLS-ULTRA-001', 'price' => 3500, 'cat' => 'Инструменты', 'devices' => []],
            ['name' => 'Термовоздушная станция', 'article' => 'TLS-HGUN-001', 'price' => 5800, 'cat' => 'Инструменты', 'devices' => []],
        ];

        foreach ($others as $o) {
            $product = Product::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $o['name'],
                'article' => $o['article'],
                'category_id' => $categories[$o['cat']]->id,
                'unit' => 'pcs',
                'purchase_price' => $o['price'],
                'status' => 'active',
                'type' => $o['type'] ?? 'regular',
                'track_serials' => $o['serials'] ?? false,
                'min_stock' => 2,
                'max_stock' => 15,
            ]);

            if (!empty($o['devices'])) {
                $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $o['devices']);
                $product->deviceModels()->attach($modelIds);
            }
        }
    }

    private function createSamsungProducts(Tenant $tenant, array $chars, array $deviceModels): void
    {
        $categories = [];
        foreach (['Дисплеи', 'Аккумуляторы', 'Платы', 'Камеры', 'Расходники'] as $i => $name) {
            $categories[$name] = ProductCategory::create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'sort_order' => $i,
            ]);
        }

        $displays = [
            ['name' => 'Дисплей Samsung S24 Ultra AMOLED', 'article' => 'DSP-SS24U-001', 'price' => 6500, 'color' => 'Чёрный', 'size' => '6.8', 'matrix' => 'AMOLED', 'devices' => [13]],
            ['name' => 'Дисплей Samsung S23 AMOLED', 'article' => 'DSP-SS23-001', 'price' => 3500, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'AMOLED', 'devices' => [10, 11]],
            ['name' => 'Дисплей Samsung S22 AMOLED', 'article' => 'DSP-SS22-001', 'price' => 2900, 'color' => 'Чёрный', 'size' => '6.1', 'matrix' => 'AMOLED', 'devices' => [8, 9]],
            ['name' => 'Дисплей Samsung S24 AMOLED', 'article' => 'DSP-SS24-001', 'price' => 4200, 'color' => 'Чёрный', 'size' => '6.2', 'matrix' => 'AMOLED', 'devices' => [12]],
        ];

        foreach ($displays as $d) {
            $product = Product::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $d['name'],
                'article' => $d['article'],
                'barcode' => fake()->ean13(),
                'category_id' => $categories['Дисплеи']->id,
                'unit' => 'pcs',
                'purchase_price' => $d['price'],
                'status' => 'active',
                'type' => 'component',
                'track_serials' => true,
                'min_stock' => 3,
                'max_stock' => 15,
            ]);

            $product->characteristicValues()->createMany([
                ['tenant_id' => $tenant->id, 'characteristic_id' => $chars['Цвет']->id, 'value' => $d['color']],
                ['tenant_id' => $tenant->id, 'characteristic_id' => $chars['Размер экрана']->id, 'value' => $d['size']],
                ['tenant_id' => $tenant->id, 'characteristic_id' => $chars['Тип матрицы']->id, 'value' => $d['matrix']],
            ]);

            $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $d['devices']);
            $product->deviceModels()->attach($modelIds);
        }

        $batteries = [
            ['name' => 'Аккумулятор Samsung S24 Ultra', 'article' => 'BAT-SS24U-001', 'price' => 1100, 'capacity' => '5000', 'devices' => [13]],
            ['name' => 'Аккумулятор Samsung S23', 'article' => 'BAT-SS23-001', 'price' => 800, 'capacity' => '3900', 'devices' => [10, 11]],
            ['name' => 'Аккумулятор Samsung S22', 'article' => 'BAT-SS22-001', 'price' => 700, 'capacity' => '3700', 'devices' => [8, 9]],
        ];

        foreach ($batteries as $b) {
            $product = Product::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $b['name'],
                'article' => $b['article'],
                'barcode' => fake()->ean13(),
                'category_id' => $categories['Аккумуляторы']->id,
                'unit' => 'pcs',
                'purchase_price' => $b['price'],
                'status' => 'active',
                'type' => 'component',
                'track_serials' => false,
                'min_stock' => 5,
                'max_stock' => 30,
            ]);

            $product->characteristicValues()->create([
                'tenant_id' => $tenant->id,
                'characteristic_id' => $chars['Ёмкость батареи']->id,
                'value' => $b['capacity'],
            ]);

            $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $b['devices']);
            $product->deviceModels()->attach($modelIds);
        }

        $others = [
            ['name' => 'Плата зарядки Samsung S24', 'article' => 'BRD-SS24-CH01', 'price' => 380, 'cat' => 'Платы', 'devices' => [12, 13]],
            ['name' => 'Основная камера Samsung S24 Ultra', 'article' => 'CAM-SS24U-MAIN', 'price' => 3100, 'cat' => 'Камеры', 'devices' => [13]],
            ['name' => 'Рамка Samsung S23 Ultra', 'article' => 'CAS-SS23U-FRM', 'price' => 900, 'cat' => 'Дисплеи', 'devices' => [11]],
            ['name' => 'Samsung S22 донор', 'article' => 'DNR-SS22-001', 'price' => 4500, 'cat' => 'Дисплеи', 'devices' => [8], 'type' => 'donor', 'serials' => true],
            ['name' => 'Клей T-8000 50мл', 'article' => 'CON-GLUE-T8K', 'price' => 200, 'cat' => 'Расходники', 'devices' => []],
            ['name' => 'Двусторонний скотч Samsung S24', 'article' => 'CON-TAPE-SS24', 'price' => 55, 'cat' => 'Расходники', 'devices' => []],
            ['name' => 'OCA плёнка Samsung S23', 'article' => 'CON-OCA-SS23', 'price' => 70, 'cat' => 'Расходники', 'devices' => []],
            ['name' => 'Салфетки безворсовые (100 шт)', 'article' => 'CON-WIPE-100', 'price' => 150, 'cat' => 'Расходники', 'devices' => []],
            ['name' => 'Припой ПОС-61 0.5мм', 'article' => 'CON-SOLDER-05', 'price' => 280, 'cat' => 'Расходники', 'devices' => []],
        ];

        foreach ($others as $o) {
            $product = Product::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name' => $o['name'],
                'article' => $o['article'],
                'category_id' => $categories[$o['cat']]->id,
                'unit' => 'pcs',
                'purchase_price' => $o['price'],
                'status' => 'active',
                'type' => $o['type'] ?? 'regular',
                'track_serials' => $o['serials'] ?? false,
                'min_stock' => 2,
                'max_stock' => 15,
            ]);

            if (!empty($o['devices'])) {
                $modelIds = array_map(fn ($i) => $deviceModels[$i]->id, $o['devices']);
                $product->deviceModels()->attach($modelIds);
            }
        }
    }
}
