<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->enum('version', ['light', 'standard', 'pro', 'enterprise'])->default('enterprise')->after('email');
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->string('job_title')->nullable()->after('avatar_path');
            $table->string('language')->default('fr')->after('job_title');
            $table->string('timezone')->default('Africa/Libreville')->after('language');
            $table->json('preferences')->nullable()->after('timezone');
            $table->timestamp('last_login_at')->nullable()->after('preferences');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->boolean('is_active')->default(true)->after('last_login_ip');
            $table->index(['company_id', 'is_active']);
            $table->index('version');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'version', 'phone', 'avatar_path', 'job_title', 'language', 'timezone', 'preferences', 'last_login_at', 'last_login_ip', 'is_active']);
        });
    }
};
