<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            $table->text('detail_rumus')->nullable()->after('terima_bersih');
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            $table->dropColumn('detail_rumus');
        });
    }
};
