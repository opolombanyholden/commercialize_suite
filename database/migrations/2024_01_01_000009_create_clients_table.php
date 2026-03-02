<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['individual', 'business'])->default('individual');
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('GA');
            $table->string('payment_terms')->default('cash');
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->integer('orders_count')->default(0);
            $table->timestamp('last_order_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'is_active']);
            $table->index('type');
            $table->index('email');
            $table->index('phone');
            $table->fullText(['name', 'company_name', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
