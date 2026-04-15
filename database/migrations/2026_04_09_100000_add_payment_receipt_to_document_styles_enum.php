<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE document_styles MODIFY COLUMN document_type ENUM('quote', 'invoice', 'delivery_note', 'payment_receipt') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("DELETE FROM document_styles WHERE document_type = 'payment_receipt'");
        DB::statement("ALTER TABLE document_styles MODIFY COLUMN document_type ENUM('quote', 'invoice', 'delivery_note') NOT NULL");
    }
};
