<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            if (!Schema::hasColumn('data_simulasi_pelengkap', 'nama_ibu_kandung')) {
                $table->text('nama_ibu_kandung')->nullable()->after('npwp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            if (Schema::hasColumn('data_simulasi_pelengkap', 'nama_ibu_kandung')) {
                $table->dropColumn('nama_ibu_kandung');
            }
        });
    }
};
