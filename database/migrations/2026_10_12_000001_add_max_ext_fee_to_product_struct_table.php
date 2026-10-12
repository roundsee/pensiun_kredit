<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_struct', function (Blueprint $table) {
            if (! Schema::hasColumn('product_struct', 'max_ext_fee')) {
                $table->decimal('max_ext_fee', 10, 6)->nullable()->after('tata_laksana_plus_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_struct', function (Blueprint $table) {
            if (Schema::hasColumn('product_struct', 'max_ext_fee')) {
                $table->dropColumn('max_ext_fee');
            }
        });
    }
};
