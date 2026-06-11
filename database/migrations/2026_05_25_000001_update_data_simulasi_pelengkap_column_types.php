<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * @var array<int, string>
     */
    private array $dateColumns = [
        'tgl_sppk',
        'tgl_surat_pernyataan_kuasa_potong_gaji',
        'tanggal_pk',
        'tanggal_surat_kuasa_substitusi',
        'tanggal_skep',
        'tgl_kuasa_potong_gaji',
        'tgl_lahir_pasangan',
        'tanggal',
        'tanggal_substitusi',
        'tgl_awal_kredit',
        'tgl_akhir_kredit',
        'tgl_akhir_kredit_penarikan',
    ];

    /**
     * @var array<int, string>
     */
    private array $doubleColumns = [
        'suku_bunga',
        'prosentase_provisi',
        'prosentase_administrasi',
        'asuransi',
        'prosentase_admin_bank',
        'plafond',
        'biaya_provisi',
        'biaya_administrasi_kredit',
        'asuransi_jiwa_kredit',
        'biaya_flagging',
        'total_biaya',
        'angsuran_dibayar_dimuka',
        'total_penerimaan',
        'angsuran_pokok_bunga_perbulan',
        'biaya_administrasi_angsuran_perbulan_baa',
        'angsuran_bank_baa',
        'materai',
        'nominal_penarikan',
    ];

    public function up(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            foreach ($this->dateColumns as $column) {
                if (Schema::hasColumn('data_simulasi_pelengkap', $column)) {
                    $table->date($column)->nullable()->change();
                }
            }

            foreach ($this->doubleColumns as $column) {
                if (Schema::hasColumn('data_simulasi_pelengkap', $column)) {
                    $table->double($column)->nullable()->change();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            foreach ($this->doubleColumns as $column) {
                if (Schema::hasColumn('data_simulasi_pelengkap', $column)) {
                    $table->text($column)->nullable()->change();
                }
            }

            foreach ($this->dateColumns as $column) {
                if (Schema::hasColumn('data_simulasi_pelengkap', $column)) {
                    $table->text($column)->nullable()->change();
                }
            }
        });
    }
};