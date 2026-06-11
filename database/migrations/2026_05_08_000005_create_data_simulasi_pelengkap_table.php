<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('data_simulasi_pelengkap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_simulasi_id')->constrained('data_simulasi')->cascadeOnDelete()->unique();
            $table->text('no')->nullable();
            $table->text('no_pk')->nullable();
            $table->text('dibuat_di')->nullable();
            $table->text('hari')->nullable();
            $table->text('tanggal')->nullable();
            $table->text('nama')->nullable();
            $table->text('no_ktp')->nullable();
            $table->text('alamat')->nullable();
            $table->text('rt')->nullable();
            $table->text('rw')->nullable();
            $table->text('kel')->nullable();
            $table->text('kec')->nullable();
            $table->text('kota_kab')->nullable();
            $table->text('kode_pos')->nullable();
            $table->text('nama_petugas_nbp')->nullable();
            $table->text('jabatan_petugas')->nullable();
            $table->text('nomor_substitusi_pic')->nullable();
            $table->text('tanggal_substitusi')->nullable();
            $table->text('tgl_sppk')->nullable();
            $table->text('plafond')->nullable();
            $table->text('jw')->nullable();
            $table->text('suku_bunga')->nullable();
            $table->text('biaya_provisi')->nullable();
            $table->text('biaya_administrasi_kredit')->nullable();
            $table->text('asuransi_jiwa_kredit')->nullable();
            $table->text('materai')->nullable();
            $table->text('biaya_flagging')->nullable();
            $table->text('total_biaya')->nullable();
            $table->text('angsuran_dibayar_dimuka')->nullable();
            $table->text('total_penerimaan')->nullable();
            $table->text('angsuran_pokok_bunga_perbulan')->nullable();
            $table->text('biaya_administrasi_angsuran_perbulan_baa')->nullable();
            $table->text('jangka_waktu')->nullable();
            $table->text('tgl_awal_kredit')->nullable();
            $table->text('tgl_akhir_kredit')->nullable();
            $table->text('angsuran_bank_baa')->nullable();
            $table->text('terbilang_angsuran')->nullable();
            $table->text('kali_angsuran')->nullable();
            $table->text('tgl_akhir_kredit_penarikan')->nullable();
            $table->text('nominal_penarikan')->nullable();
            $table->text('terbilang_penarikan')->nullable();
            $table->text('no_skep')->nullable();
            $table->text('atas_nama_sk')->nullable();
            $table->text('tgl_kuasa_potong_gaji')->nullable();
            $table->text('atas_nama_kuasa_potong_gaji')->nullable();
            $table->text('nama_kepesertaan_ajk')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_simulasi_pelengkap');
    }
};