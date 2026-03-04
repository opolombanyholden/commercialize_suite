<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->char('public_token', 36)->nullable()->unique()->after('delivery_number');
            $table->boolean('pin_verified')->default(false)->after('signature');
            $table->timestamp('pin_verified_at')->nullable()->after('pin_verified');
            $table->string('pin_verified_by', 20)->nullable()->after('pin_verified_at'); // client | livreur
        });
    }

    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn(['public_token', 'pin_verified', 'pin_verified_at', 'pin_verified_by']);
        });
    }
};
