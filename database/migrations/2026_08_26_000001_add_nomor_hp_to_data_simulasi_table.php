<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            if (! Schema::hasColumn('data_simulasi', 'nomor_hp')) {
                $table->string('nomor_hp')->nullable()->after('nomor_pensiun');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            if (Schema::hasColumn('data_simulasi', 'nomor_hp')) {
                $table->dropColumn('nomor_hp');
            }
        });
    }
};
