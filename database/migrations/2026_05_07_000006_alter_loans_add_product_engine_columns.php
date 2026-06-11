<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('loan_number')->constrained('products')->nullOnDelete();
            $table->json('debtor_data')->nullable()->after('disbursed_at');
            $table->json('submission_data')->nullable()->after('debtor_data');
            $table->json('financial_data')->nullable()->after('submission_data');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn(['debtor_data', 'submission_data', 'financial_data']);
        });
    }
};
