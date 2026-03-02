<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('expected_quantity')->default(0); // Stock théorique
            $table->integer('good_quantity')->nullable();     // Comptage : bon état
            $table->integer('damaged_quantity')->nullable()->default(0); // Comptage : mauvais état
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['inventory_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_lines');
    }
};
