<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabel ini sudah dibuat manual di server dengan DDL yang sama, jadi
        // migration ini hanya jadi pagar pengaman kalau tabel belum ada. Kalau
        // sudah ada, strukturnya dibiarkan apa adanya supaya data yang sudah
        // masuk tidak hilang.
        if (Schema::hasTable('data_pencairan_platinum')) {
            return;
        }

        Schema::create('data_pencairan_platinum', function (Blueprint $table) {
            // `int` signed auto_increment persis seperti DDL sumber (bukan
            // `increments()` yang menghasilkan unsigned).
            $table->integer('id')->autoIncrement();
            $table->primary('id');

            $table->integer('no_urut')->nullable();
            $table->date('tgl_akad')->nullable();
            $table->string('nama', 150)->nullable();
            $table->string('nopen', 50)->nullable();
            $table->string('norek_kb', 50)->nullable();
            $table->string('no_tlp_hp', 30)->nullable();
            $table->string('no_pk', 100)->unique();
            $table->decimal('plafond', 15, 2)->nullable();
            $table->integer('tenor')->nullable();
            $table->decimal('angsuran', 15, 2)->nullable();
            $table->string('pic', 100)->nullable();
            $table->string('korwil', 100)->nullable();
            $table->string('ao_kb', 100)->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('verif', 50)->nullable();
            $table->date('tgl_cair_dp')->nullable();
            $table->date('tgl_cair_pelunasan')->nullable();
            $table->date('tgl_cair_sisa_bersih')->nullable();
            $table->decimal('nominal_cair_dp', 15, 2)->default(0);
            $table->decimal('nominal_cair_pelunasan', 15, 2)->default(0);
            $table->decimal('nominal_cair_sisa_bersih', 15, 2)->default(0);
            $table->string('pendana', 100)->nullable();
            $table->decimal('biaya_provisi', 15, 2)->default(0);
            $table->decimal('biaya_admin', 15, 2)->default(0);
            $table->decimal('biaya_asuransi', 15, 2)->default(0);
            $table->decimal('materai_tl', 15, 2)->default(0);
            $table->decimal('biaya_flagging', 15, 2)->default(0);
            $table->decimal('total_potongan', 15, 2)->default(0);
            $table->decimal('angsuran_dimuka', 15, 2)->default(0);
            $table->decimal('angsuran_per_bulan_bank', 15, 2)->default(0);
            $table->decimal('biaya_adm_angsuran', 15, 2)->default(0);
            $table->decimal('total_angsuran', 15, 2)->default(0);
            $table->decimal('fee_insentif_persen', 5, 2)->nullable();
            $table->decimal('fee_insentif_nominal', 15, 2)->default(0);
            $table->string('tat_flagging', 50)->nullable();
            $table->string('status_flagging', 100)->nullable();
            // Sumber DDL menyimpannya sebagai varchar, bukan date, jadi
            // ditanya-tanyakan biasanya berisi waktu kirim (mis. "2026-01-05 09:30").
            $table->string('tgl_kirim_email_flagging', 50)->nullable();
            $table->text('kendala_flagging')->nullable();
            $table->text('keterangan_tambahan')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('no_urut');
            $table->index('nopen');
            $table->index('tgl_akad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_pencairan_platinum');
    }
};