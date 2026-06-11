<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idpb', function (Blueprint $table) {
            $table->id();
            $table->string('nama_petugas');
            $table->string('jabatan')->nullable();
            $table->string('nomor_substitusi')->nullable();
            $table->string('tanggal_substitusi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idpb');
    }
};
