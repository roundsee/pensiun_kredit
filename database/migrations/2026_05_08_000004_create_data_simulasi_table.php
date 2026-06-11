<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('data_simulasi', function (Blueprint $table) {
            $table->id();
            $table->text('jenis_pensiun')->nullable();
            $table->text('nama_debitur')->nullable();
            $table->text('tanggal_lahir')->nullable();
            $table->text('umur')->nullable();
            $table->text('nomor_pensiun')->nullable();
            $table->text('instansi')->nullable();
            $table->text('gaji_pensiun')->nullable();
            $table->text('sisa_gaji_saat_pengajuan')->nullable();
            $table->text('produk')->nullable();
            $table->text('mutasi')->nullable();
            $table->text('bank_asal')->nullable();
            $table->text('bank_tujuan')->nullable();
            $table->text('tenor_max')->nullable();
            $table->text('plafond_max')->nullable();
            $table->text('tenor')->nullable();
            $table->text('nama_marketing')->nullable();
            $table->text('kode_area')->nullable();
            $table->text('usia_lunas')->nullable();
            $table->text('tgl_permohonan')->nullable();
            $table->text('tgl_lunas')->nullable();
            $table->text('blokir_angsuran')->nullable();
            $table->text('plafond')->nullable();
            $table->text('angsuran')->nullable();
            $table->text('biaya_adm_angs')->nullable();
            $table->text('total_angsuran')->nullable();
            $table->text('provisi')->nullable();
            $table->text('administrasi')->nullable();
            $table->text('asuransi')->nullable();
            $table->text('extra_premi')->nullable();
            $table->text('amount_blokir_angsuran')->nullable();
            $table->text('pelunasan')->nullable();
            $table->text('tata_laksana')->nullable();
            $table->text('total_biaya')->nullable();
            $table->text('sisa_gaji_akhir')->nullable();
            $table->text('terima_bersih')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_simulasi');
    }
};