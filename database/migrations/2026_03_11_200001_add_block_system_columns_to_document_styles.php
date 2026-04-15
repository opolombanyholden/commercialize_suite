<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_styles', function (Blueprint $table) {
            $table->unsignedTinyInteger('header_left_width')->default(33)->after('title_position');
            $table->unsignedTinyInteger('header_center_width')->default(34)->after('header_left_width');
            $table->unsignedTinyInteger('header_right_width')->default(33)->after('header_center_width');
            $table->unsignedTinyInteger('footer_left_width')->default(33)->after('header_right_width');
            $table->unsignedTinyInteger('footer_center_width')->default(34)->after('footer_left_width');
            $table->unsignedTinyInteger('footer_right_width')->default(33)->after('footer_center_width');
            $table->decimal('header_height_cm', 3, 1)->default(2.0)->after('footer_right_width');
            $table->decimal('footer_height_cm', 3, 1)->default(1.0)->after('header_height_cm');
            $table->text('sales_conditions')->nullable()->after('footer_content');
            $table->boolean('uses_block_system')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('document_styles', function (Blueprint $table) {
            $table->dropColumn([
                'header_left_width', 'header_center_width', 'header_right_width',
                'footer_left_width', 'footer_center_width', 'footer_right_width',
                'header_height_cm', 'footer_height_cm',
                'sales_conditions', 'uses_block_system',
            ]);
        });
    }
};
