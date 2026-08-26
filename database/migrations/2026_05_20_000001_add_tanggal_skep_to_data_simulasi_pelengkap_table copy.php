<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('data_simulasi_pelengkap', 'status_kawin')) {
            Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
                $table->string('status_kawin')->nullable()->after('no_skep');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('data_simulasi_pelengkap', 'status_kawin')) {
            Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
                $table->dropColumn('status_kawin');
            });
        }
    }
};
