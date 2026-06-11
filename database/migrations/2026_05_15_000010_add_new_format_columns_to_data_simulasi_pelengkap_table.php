<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * @var array<int, string>
     */
    private array $columns = [
        'no_sppk',
        'kota',
        'alamat_2',
        'jenis_fasilitas',
        'bentuk_fasilitas',
        'prosentase_provisi',
        'prosentase_administrasi',
        'asuransi',
        'tgl_surat_pernyataan_kuasa_potong_gaji',
        'tanggal_pk',
        'nama_perwakilan_kb',
        'jabatan',
        'no_surat_kuasa_substitusi',
        'tanggal_surat_kuasa_substitusi',
        'no_hp',
        'prosentase_admin_bank',
        'norek',
        'cabang_kb',
        'nama_ao',
        'kode_ao',
        'ket',
        'nama_pasangan',
        'ktp_pasangan',
        'tgl_lahir_pasangan',
        'cabang',
        'no_si',
    ];

    public function up(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            foreach ($this->columns as $column) {
                if (!Schema::hasColumn('data_simulasi_pelengkap', $column)) {
                    $table->text($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            foreach ($this->columns as $column) {
                if (Schema::hasColumn('data_simulasi_pelengkap', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
