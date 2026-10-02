<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel profile debitur.
 *
 * Skema ini sengaja mengikuti tabel `debitur` sistem lama persis, termasuk nama
 * kolom dengan typo warisan (`prop_domilsili`, `kota_domiisili`) karena form dan
 * query existing sudah mengacu pada nama kolom tersebut.
 *
 * Kolom wilayah (prop_rumah, kota_rumah, kec_rumah, kel_rumah, prop_domilsili,
 * kota_domiisili, kec_domisili, kel_domisili, propinsipas, kotapas, kecamatanpas,
 * kelurahanpas) disimpan sebagai KODE BPS, bukan nama. Nama selalu bisa diturunkan
 * dari kode lewat tabel referensi, sedangkan nama tidak muat di varchar(10).
 *
 * Relasi ke simulasi: `simulasi.nomor_pensiun = debitur.nopen` (satu nopen bisa
 * dipakai banyak simulasi), jadi tabel ini sengaja tidak punya FK ke data_simulasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debitur', function (Blueprint $table) {
            $table->string('unit_pelayanan', 75)->nullable();
            $table->string('email_debitur', 75)->nullable();
            $table->string('referal', 75)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('nik', 16)->nullable();
            $table->string('nama_ktp', 75)->nullable();
            $table->string('tempat_lahir', 75)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('nopen', 30)->nullable();
            $table->string('nomer_sk', 100)->nullable();
            $table->date('tgl_sk')->nullable();
            $table->string('nama_karip', 75)->nullable();
            $table->string('jenis_kelamin', 5)->nullable();
            $table->string('status_pernikahan', 11)->nullable();
            $table->string('agama', 20)->nullable();
            $table->string('pekerjaan', 75)->nullable();
            $table->string('pendidikan', 30)->nullable();
            $table->string('status_rumah', 30)->nullable();
            $table->string('alamat_rumah', 255)->nullable();
            // `int` signed auto_increment persis seperti di sistem lama (bukan
            // `increments()` yang menghasilkan unsigned).
            $table->integer('id')->autoIncrement();
            $table->primary('id');
            $table->string('prop_rumah', 10)->nullable();
            $table->string('kota_rumah', 10)->nullable();
            $table->string('kec_rumah', 10)->nullable();
            $table->string('kel_rumah', 10)->nullable();
            $table->string('kodepos', 10)->nullable();
            $table->string('geotag', 50)->nullable();
            $table->string('alamat_domisili', 255)->nullable();
            $table->string('prop_domilsili', 10)->nullable();
            $table->string('kota_domiisili', 10)->nullable();
            $table->string('kec_domisili', 10)->nullable();
            $table->string('kel_domisili', 10)->nullable();
            $table->string('hp', 30)->nullable();
            $table->string('ibu_kandung', 75)->nullable();
            $table->string('nama_pasangan', 75)->nullable();
            $table->string('nik_pasangan', 16)->nullable();
            $table->string('hp_pasangan', 30)->nullable();
            $table->date('tgl_lahir_pas')->nullable();
            $table->string('alamatpas', 150)->nullable();
            $table->string('propinsipas', 20)->nullable();
            $table->string('kotapas', 20)->nullable();
            $table->string('kecamatanpas', 20)->nullable();
            $table->string('kelurahanpas', 20)->nullable();
            $table->string('kodepospas', 20)->nullable();
            $table->string('pendamping', 75)->nullable();
            $table->string('hp_pendamping', 30)->nullable();
            $table->string('hubungan', 75)->nullable();
            $table->string('kontaknama', 75)->nullable();
            $table->string('kontaktelepon', 30)->nullable();
            $table->string('kontakhubungan', 30)->nullable();
            $table->string('petugas', 75)->nullable();
            $table->string('alamat_skrg', 30)->nullable();
            $table->string('instansi_pensiun', 30)->nullable();
            $table->string('juru_bayar', 75)->nullable();
            $table->double('gaji')->nullable();
            $table->double('sisa_gaji')->nullable();
            $table->double('plafon')->nullable();
            $table->string('norek', 30)->nullable();
            $table->string('bank', 75)->nullable();
            $table->string('atas_nama', 75)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->string('propinsi_code', 10)->nullable();
            $table->string('propinsi_domisili_code', 10)->nullable();
            $table->string('kota_code', 10)->nullable();
            $table->string('kota_domisili_code', 10)->nullable();
            $table->string('kec_code', 10)->nullable();
            $table->string('kec_domisili_code', 10)->nullable();
            $table->string('kel_code', 10)->nullable();
            $table->string('kel_domisili_code', 10)->nullable();
            $table->string('norek_mnc', 30)->nullable();
            $table->string('dp_bank', 75)->nullable();
            $table->string('dp_norek', 30)->nullable();
            $table->string('dp_namarek', 75)->nullable();
            $table->double('dp_amount')->nullable();
            $table->date('to_tgl')->nullable();
            $table->string('to_bank', 75)->nullable();
            $table->string('to_norek', 30)->nullable();
            $table->string('to_namarek', 75)->nullable();
            $table->double('to_amount')->nullable();
            $table->string('tb_bank', 75)->nullable();
            $table->string('tb_norek', 30)->nullable();
            $table->string('tb_namarek', 75)->nullable();
            $table->double('tb_amount')->nullable();
            $table->string('nama_kk', 75)->nullable();
            $table->date('tgl_lahir_kk')->nullable();
            $table->string('nama_ibu_kandung_kk', 75)->nullable();
            $table->string('tempat_lahir_kk', 50)->nullable();
            $table->integer('imported')->nullable();
            $table->string('aw_nama', 75)->nullable();
            $table->string('aw_tempat_lahir', 50)->nullable();
            $table->date('aw_tgl_lahir')->nullable();
            $table->string('aw_pekerjaan', 50)->nullable();
            $table->string('aw_alamat', 200)->nullable();
            $table->index(['prop_rumah'], 'prorumah');
            $table->index(['kota_rumah'], 'kota_rumah');
            $table->index(['kec_rumah'], 'kec_rumah');
            $table->index(['kel_rumah'], 'kelrumah');
            $table->index(['prop_domilsili'], 'prodomisili');
            $table->index(['kota_domiisili'], 'kotadoomisili');
            $table->index(['kec_domisili'], 'kecdomisili');
            $table->index(['kel_domisili'], 'keledomisili');
            $table->index('nopen', 'debitur_nopen_index');
            $table->index('nik', 'debitur_nik_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debitur');
    }
};
