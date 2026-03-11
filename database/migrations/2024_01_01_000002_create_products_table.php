<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('article', 100)->unique();
            $table->string('barcode', 50)->nullable()->index();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('unit', 10)->default('pcs');
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->string('type', 20)->default('regular')->index();
            $table->boolean('track_serials')->default(false);
            $table->decimal('length_cm', 8, 2)->nullable();
            $table->decimal('width_cm', 8, 2)->nullable();
            $table->decimal('height_cm', 8, 2)->nullable();
            $table->decimal('weight_kg', 8, 3)->nullable();
            $table->integer('min_stock')->nullable();
            $table->integer('max_stock')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
