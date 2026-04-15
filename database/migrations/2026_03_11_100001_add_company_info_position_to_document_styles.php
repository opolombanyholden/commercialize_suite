<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_styles', function (Blueprint $table) {
            $table->enum('company_info_position', ['left', 'center', 'right'])
                  ->default('left')
                  ->after('logo_position');
        });
    }

    public function down(): void
    {
        Schema::table('document_styles', function (Blueprint $table) {
            $table->dropColumn('company_info_position');
        });
    }
};
