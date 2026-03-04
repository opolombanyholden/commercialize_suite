<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('discount_type')->nullable()->after('subtotal');
            $table->decimal('discount_value', 15, 2)->default(0)->after('discount_type');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_value');
            $table->foreignId('promo_id')->nullable()->after('discount_amount')
                  ->constrained('promotions')->nullOnDelete();
            $table->string('promo_code')->nullable()->after('promo_id');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropColumn(['discount_type', 'discount_value', 'discount_amount', 'promo_id', 'promo_code']);
        });
    }
};
