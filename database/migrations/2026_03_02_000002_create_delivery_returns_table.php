<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('delivery_note_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();

            $table->string('return_number')->unique();
            $table->string('client_name');

            $table->enum('status', ['pending', 'received', 'resolved'])->default('pending');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->enum('resolution', ['re_delivery', 'credit_note'])->nullable();
            $table->unsignedBigInteger('new_delivery_id')->nullable();
            $table->unsignedBigInteger('credit_note_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('new_delivery_id')->references('id')->on('delivery_notes')->nullOnDelete();
            $table->foreign('credit_note_id')->references('id')->on('invoices')->nullOnDelete();

            $table->index(['company_id', 'status']);
            $table->index('invoice_id');
            $table->index('delivery_note_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_returns');
    }
};
