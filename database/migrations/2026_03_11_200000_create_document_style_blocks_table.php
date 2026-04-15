<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_style_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_style_id')->constrained('document_styles')->cascadeOnDelete();
            $table->enum('section', ['header', 'footer']);
            $table->enum('position', ['left', 'center', 'right']);
            $table->unsignedTinyInteger('width_percent')->default(33);
            $table->string('content_type', 30)->default('empty');
            $table->text('custom_html')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['document_style_id', 'section', 'position', 'sort_order'], 'dsb_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_style_blocks');
    }
};
