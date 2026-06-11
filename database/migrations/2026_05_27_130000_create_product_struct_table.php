<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_struct', function (Blueprint $table) {
            $table->id();
            $table->string('produk', 100);
            $table->decimal('plafond_min', 18, 2)->nullable();
            $table->decimal('plafond_max', 18, 2)->nullable();
            $table->unsignedInteger('tenor_max')->nullable();
            $table->decimal('rate_percent', 10, 6)->nullable();
            $table->decimal('provisi_percent', 10, 6)->nullable();
            $table->unsignedInteger('usia_masuk_min')->nullable();
            $table->unsignedInteger('usia_max')->nullable();
            $table->decimal('admin_percent', 10, 6)->nullable();
            $table->unsignedInteger('blokir_angsuran')->nullable();
            $table->decimal('taspen', 18, 2)->nullable();
            $table->decimal('tata_laksana', 18, 2)->nullable();
            $table->decimal('tata_laksana_plus_percent', 10, 6)->nullable();
            $table->decimal('admin_angsuran_percent', 10, 6)->nullable();
            $table->decimal('dbr_percent', 10, 6)->nullable();
            $table->decimal('asabri', 18, 2)->nullable();
            $table->unsignedInteger('usia_masuk_max')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique('produk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_struct');
    }
};
