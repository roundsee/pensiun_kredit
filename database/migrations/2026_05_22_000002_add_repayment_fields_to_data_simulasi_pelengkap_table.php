<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            if (!Schema::hasColumn('data_simulasi_pelengkap', 'tanggal_dropping')) {
                $table->date('tanggal_dropping')->nullable()->after('nama_ibu_kandung');
            }

            if (!Schema::hasColumn('data_simulasi_pelengkap', 'due_date_pertama')) {
                $table->date('due_date_pertama')->nullable()->after('tanggal_dropping');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            if (Schema::hasColumn('data_simulasi_pelengkap', 'due_date_pertama')) {
                $table->dropColumn('due_date_pertama');
            }

            if (Schema::hasColumn('data_simulasi_pelengkap', 'tanggal_dropping')) {
                $table->dropColumn('tanggal_dropping');
            }
        });
    }
};
