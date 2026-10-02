<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel reference untuk form Debitur Info.
 *
 * - Wilayah: `provinsi`, `kabupaten`, `kecamatan`, `kelurahan`.
 *   Di sistem lama tabel kelurahan bernama `desa`, dan kode-nya disimpan sebagai
 *   varchar. Di sini keempat tabel diseragamkan ke bentuk varchar yang sama supaya
 *   join ke kolom wilayah `debitur` (varchar) tidak butuh konversi tipe.
 * - Lookup kecil: `agamas`, `status_rumahs`, `status_kawins`, `pekerjaans`,
 *   `pendidikans`. Bentuknya mengikuti tabel sistem lama apa adanya.
 *
 * Isi tabel wilayah di-copy satu kali dari database sumber
 * (`config('debitur.reference_source_db')`). Kalau database sumber tidak ada,
 * tabel tetap dibuat dan tabel lookup kecil diisi dari data bawaan supaya
 * dropdown tidak pernah kosong; hanya dropdown wilayah yang kosong.
 */
return new class extends Migration
{
    /** Tabel wilayah: [tabel app, tabel sumber] */
    private const WILAYAH_SUMBER = [
        'provinsi' => 'provinsi',
        'kabupaten' => 'kabupaten',
        'kecamatan' => 'kecamatan',
        'kelurahan' => 'desa',
    ];

    /** Tabel lookup kecil: [tabel app, kolom id, kolom nama, tabel sumber, default] */
    private const LOOKUP_SUMBER = [
        'agamas' => ['id', 'agama_name', 'agamas', [
            1 => 'Islam',
            2 => 'Katholik',
            3 => 'Kristen',
            4 => 'Budha',
            5 => 'Hindu',
        ]],
        'status_rumahs' => ['id', 'status_rumah_name', 'status_rumahs', [
            1 => 'MILIK SENDIRI',
            2 => 'SEWA/ KOST',
            3 => 'MILIK KELUARGA',
            4 => 'LAINNYA',
        ]],
        'status_kawins' => ['id', 'status_name', 'status_kawins', [
            1 => 'Belum Kawin',
            2 => 'Kawin',
            3 => 'Cerai Hidup',
            4 => 'Cerai Mati',
        ]],
        'pendidikans' => ['id', 'pendidikan_name', 'pendidikans', [
            1 => 'SD',
            2 => 'SMP / SLT',
            3 => 'SMA / SLTA',
            4 => 'DIPLOMA 3',
            5 => 'S1 S2 S3',
        ]],
        'pekerjaans' => ['id', 'pekerjaan_name', 'pekerjaans', [
            1 => 'PNS',
            2 => 'TNI / POLRI',
            3 => 'Pegawai Pemerintah Daerah',
            4 => 'Karyawan Swasta',
            5 => 'Wiraswasta',
            6 => 'Pedagang',
            7 => 'Petani',
            8 => 'Nelayan',
            9 => 'Buruh',
            10 => 'Pensiunan',
            11 => 'Ibu Rumah Tangga',
            12 => 'Pelajar / Mahasiswa',
            13 => 'Guru / Dosen',
            14 => 'Tenaga Kesehatan',
            15 => 'Pegawai BPD',
            16 => 'Lainnya',
        ]],
    ];

    public function up(): void
    {
        $this->createWilayahTables();
        $this->createLookupTables();

        $sumber = $this->sumberDb();
        $sumberTersedia = $sumber !== null && $this->cekSchema($sumber);

        if ($sumberTersedia) {
            $this->copyWilayah($sumber);
            $this->copyLookup($sumber);
        }

        $this->pastikanLookupTerisi();
    }

    public function down(): void
    {
        foreach (array_keys(self::WILAYAH_SUMBER) as $table) {
            Schema::dropIfExists($table);
        }

        foreach (array_keys(self::LOOKUP_SUMBER) as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function createWilayahTables(): void
    {
        foreach (array_keys(self::WILAYAH_SUMBER) as $table) {
            if (Schema::hasTable($table)) {
                continue;
            }

            Schema::create($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->string('code', 10)->nullable();
                $blueprint->string('parent_code', 12)->nullable();
                $blueprint->string('name', 100)->nullable();

                $blueprint->unique('code', $table . '_code_unique');
                $blueprint->index('parent_code', $table . '_parent_code_index');
            });
        }
    }

    private function createLookupTables(): void
    {
        foreach (self::LOOKUP_SUMBER as $table => $def) {
            [, $kolomNama] = $def;

            if (Schema::hasTable($table)) {
                continue;
            }

            Schema::create($table, function (Blueprint $blueprint) use ($kolomNama) {
                $blueprint->increments('id');
                $blueprint->string($kolomNama, 50)->nullable();
            });
        }
    }

    private function copyWilayah(string $sumber): void
    {
        foreach (self::WILAYAH_SUMBER as $tujuan => $asal) {
            if (!$this->cekTabel($sumber, $asal)) {
                continue;
            }

            DB::statement(
                "INSERT IGNORE INTO `{$tujuan}` (`code`, `parent_code`, `name`)
                 SELECT `code`, `parent_code`, `name` FROM `{$sumber}`.`{$asal}`"
            );
        }
    }

    private function copyLookup(string $sumber): void
    {
        foreach (self::LOOKUP_SUMBER as $tujuan => [$idCol, $namaCol, $asal, $default]) {
            if ($asal === null || !$this->cekTabel($sumber, $asal)) {
                continue;
            }
            DB::statement(
                "INSERT IGNORE INTO `{$tujuan}` (`id`, `{$namaCol}`)
                 SELECT `id`, `{$namaCol}` FROM `{$sumber}`.`{$asal}`"
            );
        }
    }

    /**
     * Pastikan setiap tabel lookup punya minimal satu baris supaya dropdown tidak
     * pernah kosong, walau database sumber tidak tersedia atau tabelnya kosong di
     * sana (mis. `pekerjaans` yang 0 baris di sistem lama).
     */
    private function pastikanLookupTerisi(): void
    {
        foreach (self::LOOKUP_SUMBER as $table => [$idCol, $namaCol, $asal, $default]) {
            if (DB::table($table)->count() > 0) {
                continue;
            }

            foreach ($default as $id => $nama) {
                DB::table($table)->insertOrIgnore([
                    $idCol => $id,
                    $namaCol => $nama,
                ]);
            }
        }
    }

    private function sumberDb(): ?string
    {
        $sumber = config('debitur.reference_source_db');

        if (!is_string($sumber)) {
            return null;
        }

        $sumber = trim($sumber);

        return $sumber === '' ? null : $sumber;
    }

    private function cekSchema(string $schema): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return false;
        }

        return DB::selectOne(
            'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$schema]
        ) !== null;
    }

    private function cekTabel(string $schema, string $table): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return false;
        }

        return DB::selectOne(
            'SELECT TABLE_NAME FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$schema, $table]
        ) !== null;
    }
};
