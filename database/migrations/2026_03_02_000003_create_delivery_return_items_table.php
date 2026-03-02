<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_return_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->text('description');
            $table->decimal('quantity_returned', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->string('unit')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('delivery_return_id')->references('id')->on('delivery_returns')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->index('delivery_return_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_return_items');
    }
};
