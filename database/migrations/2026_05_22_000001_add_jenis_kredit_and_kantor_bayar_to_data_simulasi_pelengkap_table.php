<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            if (!Schema::hasColumn('data_simulasi_pelengkap', 'jenis_kredit')) {
                $table->string('jenis_kredit')->nullable()->after('nama_ibu_kandung');
            }

            if (!Schema::hasColumn('data_simulasi_pelengkap', 'kantor_bayar')) {
                $table->string('kantor_bayar')->nullable()->after('jenis_kredit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            if (Schema::hasColumn('data_simulasi_pelengkap', 'kantor_bayar')) {
                $table->dropColumn('kantor_bayar');
            }

            if (Schema::hasColumn('data_simulasi_pelengkap', 'jenis_kredit')) {
                $table->dropColumn('jenis_kredit');
            }
        });
    }
};
