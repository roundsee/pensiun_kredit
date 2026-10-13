<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tata Laksana bisa bernilai persen dengan 3 angka di belakang koma
        // (mis. 3.5% = 0.035), sehingga presisi dinaikkan dari 2 ke 3 desimal.
        Schema::table('product_struct', function (Blueprint $table) {
            $table->decimal('tata_laksana', 18, 3)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_struct', function (Blueprint $table) {
            $table->decimal('tata_laksana', 18, 2)->nullable()->change();
        });
    }
};
