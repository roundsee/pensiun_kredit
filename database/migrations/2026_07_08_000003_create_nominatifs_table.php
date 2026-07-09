<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nominatifs', function (Blueprint $table) {
            $table->id();
            $table->uuid('import_batch_id')->index();
            $table->string('source_filename')->nullable();
            $table->string('sheet_name')->nullable();
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->string('bank', 20);
            $table->unsignedInteger('row_number');

            $table->string('no')->nullable();
            $table->string('nopen')->nullable();
            $table->string('norek')->nullable();
            $table->string('tanggal')->nullable();
            $table->string('nama')->nullable();
            $table->string('mo')->nullable();
            $table->string('plafond')->nullable();
            $table->string('bunga')->nullable();
            $table->string('angsuran_ke')->nullable();
            $table->string('jw')->nullable();
            $table->string('persen_byr')->nullable();
            $table->string('baki_awal')->nullable();

            $table->string('jt_pokok')->nullable();
            $table->string('jt_bunga')->nullable();
            $table->string('jt_swjb')->nullable();
            $table->string('jt_jumlah')->nullable();

            $table->string('blm_pokok')->nullable();
            $table->string('blm_bunga')->nullable();
            $table->string('blm_swjb')->nullable();
            $table->string('blm_jumlah')->nullable();

            $table->string('terima_pokok')->nullable();
            $table->string('terima_bunga')->nullable();
            $table->string('terima_swjb')->nullable();
            $table->string('terima_jumlah')->nullable();

            $table->string('baki_akhir')->nullable();
            $table->string('kolektibilitas')->nullable();
            $table->string('status_pembayaran')->nullable();
            $table->json('row_data')->nullable();
            $table->timestamps();

            $table->index(['tahun', 'bulan', 'bank']);
            $table->index(['nopen']);
            $table->index(['norek']);
            $table->index(['nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominatifs');
    }
};