<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            if (!Schema::hasColumn('data_simulasi_pelengkap', 'status_kawin')) {
                $table->string('status_kawin')->nullable()->after('no_skep');
            }
            if (!Schema::hasColumn('data_simulasi_pelengkap', 'agama')) {
                $table->string('agama')->nullable()->after('no_skep');
            }
            if (!Schema::hasColumn('data_simulasi_pelengkap', 'pendidikan')) {
                $table->string('pendidikan')->nullable()->after('no_skep');
            }
            if (!Schema::hasColumn('data_simulasi_pelengkap', 'status_rumah')) {
                $table->string('status_rumah')->nullable()->after('no_skep');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            if (Schema::hasColumn('data_simulasi_pelengkap', 'status_kawin')) {
                $table->dropColumn('status_kawin');
            }
            if (Schema::hasColumn('data_simulasi_pelengkap', 'agama')) {
                $table->dropColumn('agama');
            }
            if (Schema::hasColumn('data_simulasi_pelengkap', 'pendidikan')) {
                $table->dropColumn('pendidikan');
            }
            if (Schema::hasColumn('data_simulasi_pelengkap', 'status_rumah')) {
                $table->dropColumn('status_rumah');
            }
        });
    }
};
