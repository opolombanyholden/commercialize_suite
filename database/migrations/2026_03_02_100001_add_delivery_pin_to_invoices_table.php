<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('delivery_pin', 8)->nullable()->after('terms');
            $table->timestamp('delivery_pin_generated_at')->nullable()->after('delivery_pin');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['delivery_pin', 'delivery_pin_generated_at']);
        });
    }
};
