<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->enum('type', ['product', 'service'])->default('product');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('compare_at_price', 15, 2)->nullable();
            $table->foreignId('tax_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('track_inventory')->default(false);
            $table->decimal('stock_quantity', 10, 3)->default(0);
            $table->decimal('stock_alert_threshold', 10, 3)->nullable();
            $table->string('unit')->default('unité');
            $table->string('main_image_path')->nullable();
            $table->boolean('is_published_online')->default(false);
            $table->string('share_title')->nullable();
            $table->text('share_description')->nullable();
            $table->string('share_image_path')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->boolean('has_variants')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('sales_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'is_active', 'is_published_online']);
            $table->index('category_id');
            $table->index('type');
            $table->index('sku');
            $table->index('barcode');
            $table->unique(['company_id', 'slug']);
            $table->fullText(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
