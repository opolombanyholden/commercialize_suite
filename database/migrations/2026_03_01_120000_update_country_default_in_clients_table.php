<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mettre à jour les enregistrements existants avec le code ISO 'GA'
        DB::table('clients')->where('country', 'GA')->update(['country' => 'Gabon']);

        Schema::table('clients', function (Blueprint $table) {
            $table->string('country')->default('Gabon')->change();
        });
    }

    public function down(): void
    {
        DB::table('clients')->where('country', 'Gabon')->update(['country' => 'GA']);

        Schema::table('clients', function (Blueprint $table) {
            $table->string('country')->default('GA')->change();
        });
    }
};
