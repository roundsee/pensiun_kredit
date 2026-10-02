<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debitur extends Model
{
    protected $table = 'debitur';

    protected $fillable = [
        'unit_pelayanan',
        'email_debitur',
        'referal',
        'tanggal',
        'nik',
        'nama_ktp',
        'tempat_lahir',
        'tgl_lahir',
        'nopen',
        'nomer_sk',
        'tgl_sk',
        'nama_karip',
        'jenis_kelamin',
        'status_pernikahan',
        'agama',
        'pekerjaan',
        'pendidikan',
        'status_rumah',
        'alamat_rumah',
        'prop_rumah',
        'kota_rumah',
        'kec_rumah',
        'kel_rumah',
        'kodepos',
        'geotag',
        'alamat_domisili',
        'prop_domilsili',
        'kota_domiisili',
        'kec_domisili',
        'kel_domisili',
        'hp',
        'ibu_kandung',
        'nama_pasangan',
        'nik_pasangan',
        'hp_pasangan',
        'tgl_lahir_pas',
        'alamatpas',
        'propinsipas',
        'kotapas',
        'kecamatanpas',
        'kelurahanpas',
        'kodepospas',
        'pendamping',
        'hp_pendamping',
        'hubungan',
        'kontaknama',
        'kontaktelepon',
        'kontakhubungan',
        'petugas',
        'alamat_skrg',
        'instansi_pensiun',
        'juru_bayar',
        'gaji',
        'sisa_gaji',
        'plafon',
        'norek',
        'bank',
        'atas_nama',
        'propinsi_code',
        'propinsi_domisili_code',
        'kota_code',
        'kota_domisili_code',
        'kec_code',
        'kec_domisili_code',
        'kel_code',
        'kel_domisili_code',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tgl_lahir' => 'date',
        'tgl_sk' => 'date',
        'tgl_lahir_pas' => 'date',
        'gaji' => 'float',
        'sisa_gaji' => 'float',
        'plafon' => 'float',
    ];

    /**
     * Satu profile debitur bisa dipakai banyak simulasi karena kuncinya `nopen`.
     */
    public function simulasi(): HasMany
    {
        return $this->hasMany(DataSimulasi::class, 'nomor_pensiun', 'nopen');
    }

    public function getRouteKeyName(): string
    {
        return 'nopen';
    }

    /**
     * Ambil profile debitur berdasarkan nomor pensiun. Mengembalikan null kalau
     * nopen kosong supaya tidak pernah mencocokkan baris dengan nopen NULL/kosong.
     */
    public static function findByNopen(?string $nopen): ?self
    {
        $nopen = trim((string) $nopen);

        if ($nopen === '') {
            return null;
        }

        return static::where('nopen', $nopen)->first();
    }

    /**
     * Pastikan ada satu baris debitur untuk nopen tersebut. Dipanggil saat
     * simulasi di-confirm; baris hanya dibuat kalau belum ada, jadi data yang
     * sudah diisi user tidak pernah tertimpa.
     */
    public static function ensureForNopen(?string $nopen, array $prefill = []): ?self
    {
        $nopen = trim((string) $nopen);

        if ($nopen === '') {
            return null;
        }

        $existing = static::where('nopen', $nopen)->first();

        if ($existing !== null) {
            return $existing;
        }

        $prefill = array_intersect_key($prefill, array_flip((new static())->getFillable()));
        $prefill['nopen'] = $nopen;

        return static::create($prefill);
    }

    /**
     * Susun payload awal debitur dari sebuah simulasi, dipakai saat auto-insert
     * baris debitur ketika simulasi di-confirm.
     */
    public static function prefillDariSimulasi(DataSimulasi $simulasi): array
    {
        return [
            'nama_ktp' => $simulasi->nama_debitur,
            'tgl_lahir' => $simulasi->tanggal_lahir?->format('Y-m-d'),
            'nopen' => $simulasi->nomor_pensiun,
            'hp' => $simulasi->nomor_hp,
            'instansi_pensiun' => $simulasi->instansi,
            'gaji' => $simulasi->gaji_pensiun,
            'sisa_gaji' => $simulasi->sisa_gaji_saat_pengajuan,
            'plafon' => $simulasi->plafond,
            'bank' => $simulasi->bank_tujuan,
            'nama_karip' => $simulasi->nama_marketing,
            'alamat_skrg' => $simulasi->kode_area,
            'tanggal' => now()->format('Y-m-d'),
        ];
    }

    /**
     * Profile debitur milik sebuah simulasi, dicocokkan lewat nomor pensiun.
     * Baris yang sudah ada tidak pernah ditimpa, jadi data yang sudah diisi
     * admin aman walau simulasi di-confirm ulang.
     */
    public static function untukSimulasi(DataSimulasi $simulasi): ?self
    {
        return static::ensureForNopen(
            $simulasi->nomor_pensiun,
            static::prefillDariSimulasi($simulasi)
        );
    }
}
