<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            $table->decimal('tata_laksana_plus_percent_override', 10, 4)->nullable()->after('ext_tatalaksana');
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            $table->dropColumn('tata_laksana_plus_percent_override');
        });
    }
};
