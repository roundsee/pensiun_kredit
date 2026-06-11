<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_struct', function (Blueprint $table) {
            $table->string('kantor_bayar')->nullable()->after('produk');
        });
    }

    public function down(): void
    {
        Schema::table('product_struct', function (Blueprint $table) {
            $table->dropColumn(['kantor_bayar', 'admin_angsuran_percent_override']);
        });
    }
};
