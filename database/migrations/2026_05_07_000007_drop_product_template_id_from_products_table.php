<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'product_template_id')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_template_id']);
            $table->dropColumn('product_template_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'product_template_id')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('product_template_id')->nullable()->constrained('product_templates')->nullOnDelete();
        });
    }
};
