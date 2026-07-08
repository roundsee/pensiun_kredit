<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('banpots', function (Blueprint $table) {
            $table->id();
            $table->uuid('import_batch_id')->index();
            $table->string('source_filename')->nullable();
            $table->string('sheet_name')->nullable();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->string('bank', 20);
            $table->unsignedInteger('row_number');
            $table->string('no')->nullable();
            $table->string('no_pk')->nullable();
            $table->string('nopen')->nullable();
            $table->string('norek')->nullable();
            $table->string('total')->nullable();
            $table->string('cabang')->nullable();
            $table->string('status')->nullable();
            $table->string('plafond')->nullable();
            $table->string('selisih')->nullable();
            $table->string('giro_mitra')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('nama_debitur')->nullable();
            $table->string('jenis_tagihan')->nullable();
            $table->string('pengelola_pensiun')->nullable();
            $table->string('pendebetan_angsuran')->nullable();
            $table->string('nama_mitra_channeling')->nullable();
            $table->json('row_data')->nullable();
            $table->timestamps();

            $table->index(['tahun', 'bulan', 'bank']);
            $table->index(['nama_debitur']);
            $table->index(['status']);
            $table->index(['cabang']);
            $table->index(['pengelola_pensiun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banpots');
    }
};