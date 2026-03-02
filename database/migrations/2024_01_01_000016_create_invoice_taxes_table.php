<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('tax_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tax_name');
            $table->decimal('tax_rate', 5, 2);
            $table->enum('apply_to', ['all', 'products', 'services'])->default('all');
            $table->decimal('taxable_base', 15, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_taxes');
    }
};
