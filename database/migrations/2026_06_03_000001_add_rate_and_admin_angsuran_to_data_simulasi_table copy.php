<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            $table->decimal('rate_percent_override', 10, 6)->nullable()->after('produk');
            $table->decimal('admin_angsuran_percent_override', 10, 6)->nullable()->after('rate_percent_override');
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi', function (Blueprint $table) {
            $table->dropColumn(['rate_percent_override', 'admin_angsuran_percent_override']);
        });
    }
};
