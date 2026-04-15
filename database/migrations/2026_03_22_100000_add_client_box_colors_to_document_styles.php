<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_styles', function (Blueprint $table) {
            $table->string('client_box_bg_color', 7)->nullable()->after('table_font_style');
            $table->string('client_box_border_color', 7)->nullable()->after('client_box_bg_color');
        });
    }

    public function down(): void
    {
        Schema::table('document_styles', function (Blueprint $table) {
            $table->dropColumn(['client_box_bg_color', 'client_box_border_color']);
        });
    }
};
