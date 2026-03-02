<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('rate', 5, 2);
            $table->text('description')->nullable();
            $table->enum('apply_to', ['all', 'products', 'services'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'is_active']);
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
