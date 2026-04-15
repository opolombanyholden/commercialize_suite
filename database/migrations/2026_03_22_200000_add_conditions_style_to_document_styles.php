<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_styles', function (Blueprint $table) {
            $table->string('conditions_bg_color', 7)->nullable()->after('client_box_border_color');
            $table->string('conditions_border_color', 7)->nullable()->after('conditions_bg_color');
            $table->unsignedTinyInteger('conditions_width')->nullable()->after('conditions_border_color');
        });
    }

    public function down(): void
    {
        Schema::table('document_styles', function (Blueprint $table) {
            $table->dropColumn(['conditions_bg_color', 'conditions_border_color', 'conditions_width']);
        });
    }
};
