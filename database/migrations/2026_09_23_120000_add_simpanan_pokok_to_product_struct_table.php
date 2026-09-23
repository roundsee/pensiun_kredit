<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_struct', function (Blueprint $table) {
            if (! Schema::hasColumn('product_struct', 'simpanan_pokok')) {
                $table->decimal('simpanan_pokok', 18, 2)->nullable()->default(0)->after('blokir_angsuran');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_struct', function (Blueprint $table) {
            if (Schema::hasColumn('product_struct', 'simpanan_pokok')) {
                $table->dropColumn('simpanan_pokok');
            }
        });
    }
};
