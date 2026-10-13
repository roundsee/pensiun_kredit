<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // BRI hanya memiliki produk Platinum (tanpa Regular). Agar usia yang
        // sebelumnya masuk kategori Regular tetap bisa disimulasikan lewat
        // produk Platinum, batas usia masuk minimum BRI disamakan dengan Regular.
        DB::table('product_struct')
            ->where('produk', 'BRI-Platinum-Sendiri')
            ->update(['usia_masuk_min' => 55]);
    }

    public function down(): void
    {
        DB::table('product_struct')
            ->where('produk', 'BRI-Platinum-Sendiri')
            ->update(['usia_masuk_min' => 68]);
    }
};
