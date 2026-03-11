<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_characteristic_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('characteristic_id')->constrained('product_characteristics')->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();

            $table->unique(['product_id', 'characteristic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_characteristic_values');
    }
};
