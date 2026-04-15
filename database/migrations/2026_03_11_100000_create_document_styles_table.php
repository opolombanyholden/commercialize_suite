<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_styles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['quote', 'invoice', 'delivery_note']);

            // Header
            $table->text('header_content')->nullable();
            $table->enum('logo_position', ['left', 'center', 'right'])->default('left');
            $table->enum('title_position', ['left', 'center', 'right'])->default('right');

            // Footer
            $table->text('footer_content')->nullable();

            // Background
            $table->string('background_color', 7)->nullable();
            $table->string('background_image', 255)->nullable();

            // Colors
            $table->string('primary_color', 7)->nullable();
            $table->string('table_header_color', 7)->nullable();
            $table->string('table_odd_row_color', 7)->default('#FFFFFF');
            $table->string('table_even_row_color', 7)->default('#FAFAFA');
            $table->string('table_text_color', 7)->default('#333333');

            // Typography
            $table->string('table_font_family', 50)->default('DejaVu Sans');
            $table->enum('table_font_style', ['normal', 'bold', 'italic', 'bold_italic'])->default('normal');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_styles');
    }
};
