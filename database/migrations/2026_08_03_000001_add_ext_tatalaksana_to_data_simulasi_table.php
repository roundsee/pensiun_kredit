<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            $table->decimal('ext_tatalaksana', 18, 2)->nullable()->after('tata_laksana');
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            $table->dropColumn('ext_tatalaksana');
        });
    }
};
