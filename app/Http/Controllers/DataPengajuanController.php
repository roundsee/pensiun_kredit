<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DefinesDebiturForm;
use App\Models\DataSimulasi;
use App\Models\Debitur;
use Illuminate\Http\Request;

/**
 * Halaman kerja pengajuan: satu list ringkas untuk mencari debitur, lalu satu
 * form yang memegang semua aktivitas debitur dan pengajuan pinjamannya.
 *
 * List sengaja hanya punya dua tombol (Info dan Download PDF). Semua aksi
 * lainnya dipindah ke form, supaya tabel tidak lagi melebar jadi puluhan
 * tombol per baris.
 */
class DataPengajuanController extends Controller
{
    use DefinesDebiturForm;

    /** Pilihan filter status. Nilai kosong berarti status NULL. */
    private const STATUS = [
        'confirmed' => 'Confirmed',
        'trial' => 'Trial',
        'kosong' => 'Tanpa Status',
        'semua' => 'Semua Status',
    ];

    public function index(Request $request)
    {
        $filters = [
            'nopen' => trim((string) $request->query('nopen', '')),
            'nama' => trim((string) $request->query('nama', '')),
            'nomor_hp' => trim((string) $request->query('nomor_hp', '')),
            'status' => trim((string) $request->query('status', 'confirmed')),
        ];

        if (! array_key_exists($filters['status'], self::STATUS)) {
            $filters['status'] = 'confirmed';
        }

        $dataSimulasi = DataSimulasi::query()
            ->with('pelengkap')
            ->tap(function ($query) use ($filters) {
                // Status NULL dianggap sudah confirmed supaya tidak hilang dari
                // list, sama seperti perilaku list Data Simulasi yang lama.
                if ($filters['status'] === 'confirmed') {
                    $query->where(fn ($inner) => $inner->where('status', 'confirmed')->orWhereNull('status'));
                } elseif ($filters['status'] === 'trial') {
                    $query->where('status', 'trial');
                } elseif ($filters['status'] === 'kosong') {
                    $query->whereNull('status');
                }
            })
            ->when($filters['nopen'] !== '', fn ($query) => $query->where('nomor_pensiun', 'like', '%' . $filters['nopen'] . '%'))
            ->when($filters['nama'] !== '', fn ($query) => $query->where('nama_debitur', 'like', '%' . $filters['nama'] . '%'))
            ->when($filters['nomor_hp'] !== '', fn ($query) => $query->where('nomor_hp', 'like', '%' . $filters['nomor_hp'] . '%'))
            ->latest('id')
            ->paginate(config('debitur.per_page', 15))
            ->appends($filters);

        return view('pengajuan.index', [
            'dataSimulasi' => $dataSimulasi,
            'filters' => $filters,
            'statusOptions' => self::STATUS,
        ]);
    }

    /**
     * Form activity untuk satu simulasi. Profile debitur ikut diambil (atau
     * dibuat) di sini supaya admin bisa isi data debitur tanpa pindah halaman.
     */
    public function info(Request $request, DataSimulasi $dataSimulasi)
    {
        $dataSimulasi->loadMissing('pelengkap');

        $debitur = filled($dataSimulasi->nomor_pensiun)
            ? Debitur::untukSimulasi($dataSimulasi)
            : null;

        return view('pengajuan.info', [
            'simulasi' => $dataSimulasi,
            'debitur' => $debitur,
            'debiturTabs' => $this->debiturTabs(),
            'debiturFieldMap' => $this->debiturFieldMap(),
            'ringkasan' => $this->ringkasan($dataSimulasi),
        ]);
    }

    /**
     * Lompat ke simulasi lain tanpa harus balik ke halaman list. Dicari dari
     * tabel simulasi dulu, lalu dari tabel debitur supaya NIK dan nama debitur
     * yang hanya ada di profil tetap ketemu.
     */
    public function cari(Request $request)
    {
        $kataKunci = trim((string) $request->query('q', ''));

        if ($kataKunci === '') {
            return redirect()->route('data_pengajuan.index');
        }

        $simulasi = $this->cariSimulasi($kataKunci);

        if ($simulasi === null) {
            return redirect()
                ->route('data_pengajuan.index')
                ->with('error', 'Simulasi dengan kata kunci "' . $kataKunci . '" tidak ditemukan.');
        }

        return redirect()->route('data_pengajuan.info', $simulasi);
    }

    private function cariSimulasi(string $kataKunci): ?DataSimulasi
    {
        $polanya = '%' . $kataKunci . '%';

        $langsung = DataSimulasi::query()
            ->where(fn ($query) => $query
                ->where('nomor_pensiun', 'like', $polanya)
                ->orWhere('nama_debitur', 'like', $polanya)
                ->orWhere('nomor_hp', 'like', $polanya))
            ->latest('id')
            ->first();

        if ($langsung !== null) {
            return $langsung;
        }

        $nopen = Debitur::query()
            ->where(fn ($query) => $query
                ->where('nopen', 'like', $polanya)
                ->orWhere('nik', 'like', $polanya)
                ->orWhere('nama_ktp', 'like', $polanya))
            ->latest('id')
            ->value('nopen');

        if (filled($nopen)) {
            return DataSimulasi::query()
                ->where('nomor_pensiun', $nopen)
                ->latest('id')
                ->first();
        }

        return null;
    }

    /**
     * Ringkasan nilai simulasi untuk tab Pengajuan, dikelompokkan biar mudah
     * dibaca. Label, format angka, dan nama kolomnya declare di sini supaya
     * view tidak perlu tahu detail kolom.
     *
     * @return array<int, array{legend: string, items: array<int, array{label: string, value: string}>}>
     */
    private function ringkasan(DataSimulasi $simulasi): array
    {
        $teks = static fn ($nilai, string $fallback = '-') => $nilai === null || $nilai === '' ? $fallback : (string) $nilai;
        $tanggal = static fn ($nilai) => $nilai?->format('d-m-Y') ?? '-';
        $angka = static fn ($nilai) => $nilai === null ? '-' : number_format((float) $nilai, 0, ',', '.');
        $persen = static fn ($nilai) => $nilai === null ? '-' : $nilai . '%';

        return [
            [
                'legend' => 'Identitas Pemohon',
                'items' => [
                    ['label' => 'ID Simulasi', 'value' => $teks($simulasi->id)],
                    ['label' => 'Status', 'value' => $teks($simulasi->status, 'Tanpa Status')],
                    ['label' => 'Keterangan', 'value' => $teks($simulasi->keterangan)],
                    ['label' => 'Nama Debitur', 'value' => $teks($simulasi->nama_debitur)],
                    ['label' => 'Nopen', 'value' => $teks($simulasi->nomor_pensiun)],
                    ['label' => 'Tanggal Lahir', 'value' => $tanggal($simulasi->tanggal_lahir)],
                    ['label' => 'Usia', 'value' => $simulasi->umur === null ? '-' : $simulasi->umur . ' th'],
                    ['label' => 'No HP', 'value' => $teks($simulasi->nomor_hp)],
                    ['label' => 'Jenis Pensiun', 'value' => $teks($simulasi->jenis_pensiun)],
                    ['label' => 'Instansi', 'value' => $teks($simulasi->instansi)],
                    ['label' => 'Marketing', 'value' => $teks($simulasi->nama_marketing)],
                ],
            ],
            [
                'legend' => 'Kredit yang Diajukan',
                'items' => [
                    ['label' => 'Produk', 'value' => $teks($simulasi->produk)],
                    ['label' => 'Bank Asal', 'value' => $teks($simulasi->bank_asal)],
                    ['label' => 'Bank Tujuan', 'value' => $teks($simulasi->bank_tujuan)],
                    ['label' => 'Kode Area', 'value' => $teks($simulasi->kode_area)],
                    ['label' => 'Mutasi', 'value' => $teks($simulasi->mutasi)],
                    ['label' => 'Plafon', 'value' => $angka($simulasi->plafond)],
                    ['label' => 'Plafon Maks', 'value' => $angka($simulasi->plafond_max)],
                    ['label' => 'Tenor', 'value' => $simulasi->tenor === null ? '-' : $simulasi->tenor . ' bulan'],
                    ['label' => 'Tenor Maks', 'value' => $simulasi->tenor_max === null ? '-' : $simulasi->tenor_max . ' bulan'],
                    ['label' => 'Angsuran', 'value' => $angka($simulasi->angsuran)],
                    ['label' => 'Rate', 'value' => $persen($simulasi->rate_percent_override)],
                    ['label' => 'Admin Angsuran', 'value' => $persen($simulasi->admin_angsuran_percent_override)],
                    ['label' => 'Angsuran Lain', 'value' => $angka($simulasi->angsuran_lain)],
                ],
            ],
            [
                'legend' => 'Gaji & Biaya',
                'items' => [
                    ['label' => 'Gaji Pensiun', 'value' => $angka($simulasi->gaji_pensiun)],
                    ['label' => 'Sisa Gaji Saat Pengajuan', 'value' => $angka($simulasi->sisa_gaji_saat_pengajuan)],
                    ['label' => 'Blokir Angsuran', 'value' => $angka($simulasi->blokir_angsuran)],
                    ['label' => 'Administrasi', 'value' => $angka($simulasi->administrasi)],
                    ['label' => 'Provisi', 'value' => $angka($simulasi->provisi)],
                    ['label' => 'Asuransi', 'value' => $angka($simulasi->asuransi)],
                    ['label' => 'Extra Premi', 'value' => $angka($simulasi->extra_premi)],
                    ['label' => 'Biaya Adm Angsuran', 'value' => $angka($simulasi->biaya_adm_angs)],
                    ['label' => 'Simpanan Pokok', 'value' => $angka($simulasi->simpanan_pokok)],
                    ['label' => 'Total Angsuran', 'value' => $angka($simulasi->total_angsuran)],
                    ['label' => 'Ext Tata Laksana', 'value' => $angka($simulasi->ext_tatalaksana)],
                    ['label' => 'Total Biaya', 'value' => $angka($simulasi->total_biaya)],
                    ['label' => 'Sisa Gaji Akhir', 'value' => $angka($simulasi->sisa_gaji_akhir)],
                    ['label' => 'Terima Bersih', 'value' => $angka($simulasi->terima_bersih)],
                ],
            ],
            [
                'legend' => 'Jadwal & Pelunasan',
                'items' => [
                    ['label' => 'Tanggal Permohonan', 'value' => $tanggal($simulasi->tgl_permohonan)],
                    ['label' => 'Tanggal Lunas', 'value' => $tanggal($simulasi->tgl_lunas)],
                    ['label' => 'Usia Lunas', 'value' => $simulasi->usia_lunas === null ? '-' : $simulasi->usia_lunas . ' th'],
                    ['label' => 'Detail Usia Lunas', 'value' => $simulasi->usia_lunas_text],
                    ['label' => 'Pelunasan', 'value' => $angka($simulasi->pelunasan)],
                    ['label' => 'Tata Laksana', 'value' => $teks($simulasi->tata_laksana)],
                ],
            ],
        ];
    }
}
