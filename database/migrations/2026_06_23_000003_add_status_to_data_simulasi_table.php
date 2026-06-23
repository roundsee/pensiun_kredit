<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            if (!Schema::hasColumn('data_simulasi', 'status')) {
                $table->string('status', 20)->default('confirmed')->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            if (Schema::hasColumn('data_simulasi', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
