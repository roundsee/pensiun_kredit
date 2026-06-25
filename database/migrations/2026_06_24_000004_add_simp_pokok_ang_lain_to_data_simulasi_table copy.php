<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            if (!Schema::hasColumn('data_simulasi', 'simpanan_pokok')) {
                
                $table->decimal('simpanan_pokok', 15, 2);
                $table->decimal('angsuran_lain', 15, 2);
                }
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            if (Schema::hasColumn('data_simulasi', 'simpanan_pokok')) {
                $table->dropColumn('simpanan_pokok');
                $table->dropColumn('angsuran_lain');
            }
        });
    }
};
