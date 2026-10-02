<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Agama;
use App\Models\Pekerjaan;
use App\Models\Pendidikan;
use App\Models\StatusKawin;
use App\Models\StatusRumah;
use App\Models\Wilayah;

/**
 * Definisi form debitur, dipakai bersama oleh halaman Debitur Info dan form
 * activity pengajuan. Dipisah ke trait supaya label, nama kolom, urutan tab,
 * dan dropdown-nya hanya punya satu sumber kebenaran.
 */
trait DefinesDebiturForm
{
    /** Kelompok cascade dropdown wilayah per kolom. */
    private const WILAYAH_GROUP = [
        'prop_rumah' => 'rumah',
        'kota_rumah' => 'rumah',
        'kec_rumah' => 'rumah',
        'kel_rumah' => 'rumah',
        'prop_domilsili' => 'domisili',
        'kota_domiisili' => 'domisili',
        'kec_domisili' => 'domisili',
        'kel_domisili' => 'domisili',
        'propinsipas' => 'pas',
        'kotapas' => 'pas',
        'kecamatanpas' => 'pas',
        'kelurahanpas' => 'pas',
    ];

    /** @var array<string, array>|null Cache definisi field per request. */
    private ?array $debiturFieldMapCache = null;

    /**
     * Definisi tab form, dipakai bersama oleh view dan validasi supaya label,
     * nama kolom, dan dropdown-nya tidak bisa berbeda antara keduanya.
     *
     * Tipe field:
     *   text, textarea, date, number,
     *   select  => ['source' => 'agama|status_kawin|pekerjaan|pendidikan|status_rumah|instansi_pensiun|jenis_kelamin',
     *                'wilayah' => <tabel>, 'parent' => <kolom parent> (untuk wilayah)]
     */
    public function debiturTabs(): array
    {
        return [
            [
                'id' => 'pribadi',
                'title' => 'Data Pribadi & Pensiun',
                'groups' => [
                    [
                        'legend' => 'Identitas Diri',
                        'fields' => [
                            ['name' => 'nik', 'label' => 'NIK', 'type' => 'text', 'maxlength' => 16],
                            ['name' => 'nama_ktp', 'label' => 'Nama (KTP)', 'type' => 'text', 'maxlength' => 75, 'required' => true],
                            ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'maxlength' => 75],
                            ['name' => 'tgl_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date'],
                            ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'source' => 'jenis_kelamin'],
                            ['name' => 'hp', 'label' => 'No. HP', 'type' => 'text', 'maxlength' => 30],
                            ['name' => 'email_debitur', 'label' => 'Email', 'type' => 'text', 'maxlength' => 75],
                        ],
                    ],
                    [
                        'legend' => 'Data Demografis',
                        'fields' => [
                            ['name' => 'agama', 'label' => 'Agama', 'type' => 'select', 'source' => 'agama'],
                            ['name' => 'status_pernikahan', 'label' => 'Status Pernikahan', 'type' => 'select', 'source' => 'status_kawin'],
                            ['name' => 'pekerjaan', 'label' => 'Pekerjaan', 'type' => 'select', 'source' => 'pekerjaan'],
                            ['name' => 'pendidikan', 'label' => 'Pendidikan', 'type' => 'select', 'source' => 'pendidikan'],
                            ['name' => 'status_rumah', 'label' => 'Status Rumah', 'type' => 'select', 'source' => 'status_rumah'],
                        ],
                    ],
                    [
                        'legend' => 'Data Pensiun',
                        'fields' => [
                            ['name' => 'nopen', 'label' => 'No. Pensiun (Nopen)', 'type' => 'text', 'maxlength' => 30, 'readonly' => true],
                            ['name' => 'nomer_sk', 'label' => 'Nomor SK', 'type' => 'text', 'maxlength' => 100],
                            ['name' => 'tgl_sk', 'label' => 'Tanggal SK', 'type' => 'date'],
                            ['name' => 'instansi_pensiun', 'label' => 'Instansi Pensiun', 'type' => 'select', 'source' => 'instansi_pensiun'],
                            ['name' => 'gaji', 'label' => 'Gaji Pensiun', 'type' => 'number'],
                            ['name' => 'sisa_gaji', 'label' => 'Sisa Gaji Saat Pengajuan', 'type' => 'number'],
                            ['name' => 'plafon', 'label' => 'Plafon', 'type' => 'number'],
                            ['name' => 'norek', 'label' => 'No. Rekening', 'type' => 'text', 'maxlength' => 30],
                            ['name' => 'atas_nama', 'label' => 'Atas Nama Rekening', 'type' => 'text', 'maxlength' => 75],
                            ['name' => 'petugas', 'label' => 'Petugas', 'type' => 'text', 'maxlength' => 75],
                            ['name' => 'unit_pelayanan', 'label' => 'Unit Pelayanan', 'type' => 'text', 'maxlength' => 75],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'alamat',
                'title' => 'Alamat Rumah & Domisili',
                'groups' => [
                    [
                        'legend' => 'Alamat Rumah (KTP)',
                        'fields' => [
                            ['name' => 'alamat_rumah', 'label' => 'Alamat Rumah', 'type' => 'textarea'],
                            ['name' => 'prop_rumah', 'label' => 'Provinsi Rumah', 'type' => 'select', 'wilayah' => Wilayah::PROVINSI],
                            ['name' => 'kota_rumah', 'label' => 'Kabupaten/Kota Rumah', 'type' => 'select', 'wilayah' => Wilayah::KABUPATEN, 'parent' => 'prop_rumah'],
                            ['name' => 'kec_rumah', 'label' => 'Kecamatan Rumah', 'type' => 'select', 'wilayah' => Wilayah::KECAMATAN, 'parent' => 'kota_rumah'],
                            ['name' => 'kel_rumah', 'label' => 'Kelurahan Rumah', 'type' => 'select', 'wilayah' => Wilayah::KELURAHAN, 'parent' => 'kec_rumah'],
                            ['name' => 'kodepos', 'label' => 'Kode Pos Rumah', 'type' => 'text', 'maxlength' => 10],
                        ],
                    ],
                    [
                        'legend' => 'Alamat Domisili (Domisili Saat Ini)',
                        'fields' => [
                            ['name' => 'alamat_domisili', 'label' => 'Alamat Domisili', 'type' => 'textarea'],
                            ['name' => 'prop_domilsili', 'label' => 'Provinsi Domisili', 'type' => 'select', 'wilayah' => Wilayah::PROVINSI],
                            ['name' => 'kota_domiisili', 'label' => 'Kabupaten/Kota Domisili', 'type' => 'select', 'wilayah' => Wilayah::KABUPATEN, 'parent' => 'prop_domilsili'],
                            ['name' => 'kec_domisili', 'label' => 'Kecamatan Domisili', 'type' => 'select', 'wilayah' => Wilayah::KECAMATAN, 'parent' => 'kota_domiisili'],
                            ['name' => 'kel_domisili', 'label' => 'Kelurahan Domisili', 'type' => 'select', 'wilayah' => Wilayah::KELURAHAN, 'parent' => 'kec_domisili'],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'keluarga',
                'title' => 'Keluarga & Alamat Pasangan',
                'groups' => [
                    [
                        'legend' => 'Data Keluarga',
                        'fields' => [
                            ['name' => 'ibu_kandung', 'label' => 'Nama Ibu Kandung', 'type' => 'text', 'maxlength' => 75],
                            ['name' => 'nama_pasangan', 'label' => 'Nama Pasangan', 'type' => 'text', 'maxlength' => 75],
                            ['name' => 'nik_pasangan', 'label' => 'NIK Pasangan', 'type' => 'text', 'maxlength' => 16],
                            ['name' => 'tgl_lahir_pas', 'label' => 'Tanggal Lahir Pasangan', 'type' => 'date'],
                            ['name' => 'hp_pasangan', 'label' => 'No. HP Pasangan', 'type' => 'text', 'maxlength' => 30],
                        ],
                    ],
                    [
                        'legend' => 'Alamat Pasangan',
                        'fields' => [
                            ['name' => 'alamatpas', 'label' => 'Alamat Pasangan', 'type' => 'textarea'],
                            ['name' => 'propinsipas', 'label' => 'Provinsi Pasangan', 'type' => 'select', 'wilayah' => Wilayah::PROVINSI],
                            ['name' => 'kotapas', 'label' => 'Kabupaten/Kota Pasangan', 'type' => 'select', 'wilayah' => Wilayah::KABUPATEN, 'parent' => 'propinsipas'],
                            ['name' => 'kecamatanpas', 'label' => 'Kecamatan Pasangan', 'type' => 'select', 'wilayah' => Wilayah::KECAMATAN, 'parent' => 'kotapas'],
                            ['name' => 'kelurahanpas', 'label' => 'Kelurahan Pasangan', 'type' => 'select', 'wilayah' => Wilayah::KELURAHAN, 'parent' => 'kecamatanpas'],
                            ['name' => 'kodepospas', 'label' => 'Kode Pos Pasangan', 'type' => 'text', 'maxlength' => 20],
                        ],
                    ],
                    [
                        'legend' => 'Pendamping & Kontak Darurat',
                        'fields' => [
                            ['name' => 'pendamping', 'label' => 'Nama Pendamping', 'type' => 'text', 'maxlength' => 75],
                            ['name' => 'hp_pendamping', 'label' => 'No. HP Pendamping', 'type' => 'text', 'maxlength' => 30],
                            ['name' => 'hubungan', 'label' => 'Hubungan', 'type' => 'text', 'maxlength' => 75],
                            ['name' => 'kontaknama', 'label' => 'Nama Kontak', 'type' => 'text', 'maxlength' => 75],
                            ['name' => 'kontaktelepon', 'label' => 'Telepon Kontak', 'type' => 'text', 'maxlength' => 30],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Opsi dropdown untuk seluruh select non-wilayah.
     */
    private function opsiSelect(): array
    {
        $institusi = array_values(array_filter(
            (array) config('debitur.institusi_pensiun', ['TASPEN', 'ASABRI']),
            'is_string'
        ));

        return [
            'agama' => Agama::opsi(),
            'status_kawin' => StatusKawin::opsi(),
            'pekerjaan' => Pekerjaan::opsi(),
            'pendidikan' => Pendidikan::opsi(),
            'status_rumah' => StatusRumah::opsi(),
            'instansi_pensiun' => array_combine($institusi, $institusi),
            'jenis_kelamin' => [
                'L' => 'Laki-laki',
                'P' => 'Perempuan',
            ],
        ];
    }

    /**
     * Semua field form dalam bentuk datar, lengkap dengan definisi select-nya.
     * Di-cache per request karena memuat referensi wilayah cukup berat.
     */
    public function debiturFieldMap(): array
    {
        if ($this->debiturFieldMapCache !== null) {
            return $this->debiturFieldMapCache;
        }

        $opsi = $this->opsiSelect();
        $wilayahOpsi = [];
        $fields = [];

        foreach ($this->debiturTabs() as $tab) {
            foreach ($tab['groups'] as $group) {
                foreach ($group['fields'] as $field) {
                    $fields[$field['name']] = $field;

                    if (($field['type'] ?? null) !== 'select') {
                        continue;
                    }

                    if (isset($field['source'])) {
                        $fields[$field['name']]['options'] = $opsi[$field['source']] ?? [];
                    }

                    if (isset($field['wilayah'])) {
                        // Kelompok cascade dropdown, dipakai JS untuk menyusun
                        // rantai provinsi -> kabupaten -> kecamatan -> kelurahan.
                        $fields[$field['name']]['group'] = self::WILAYAH_GROUP[$field['name']] ?? $field['name'];

                        if (!isset($field['parent'])) {
                            // Opsi dalam bentuk kode => nama, yang dibutuhkan tag select.
                            $wilayahOpsi[$field['wilayah']] ??= Wilayah::peta($field['wilayah']);
                            $fields[$field['name']]['options'] = $wilayahOpsi[$field['wilayah']];
                        }
                    }
                }
            }
        }

        return $this->debiturFieldMapCache = $fields;
    }
}
