<?php

namespace App\Services;

use App\Models\DataSimulasi;

class DocumentDataBuilderService
{
    // ─── Helpers ─────────────────────────────────────────────────────────────

    private static function pick(array $values, string $default = '_______________'): string
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $text = trim((string) $value);
            if ($text !== '') {
                return $text;
            }
        }

        return $default;
    }

    private static function formatRp(mixed $value): string
    {
        if ($value === null) {
            return 'Rp. _______________';
        }

        $text = trim((string) $value);
        if ($text === '') {
            return 'Rp. _______________';
        }

        if (stripos($text, 'rp') !== false) {
            return $text;
        }

        $digits = preg_replace('/[^\d]/', '', $text);
        if ($digits === '') {
            return 'Rp. _______________';
        }

        return 'Rp. ' . number_format((float) $digits, 0, ',', '.');
    }

    private static function formatAngsuranGabung(DataSimulasi $d): string
    {
        if ($d->angsuran === null && $d->biaya_adm_angs === null) {
            return 'Rp. ___ + Rp. ___';
        }

        $angsuran = self::toNumeric($d->angsuran);
        $adm      = self::toNumeric($d->biaya_adm_angs);

        return 'Rp. ' . number_format($angsuran, 0, ',', '.')
            . ' + Rp. ' . number_format($adm, 0, ',', '.');
    }

    private static function formatDateDisplay(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $text = trim((string) $value);
        if ($text === '') {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return $text;
        }
    }

    private static function toNumeric(mixed $value): float
    {
        $digits = preg_replace('/[^\d]/', '', trim((string) $value));

        if ($digits === '') {
            return 0;
        }

        return (float) $digits;
    }

    private static function asFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/[^0-9,.-]/', '', (string) $value);
        if ($clean === null || trim($clean) === '') {
            return null;
        }

        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }

    private static function calculatePmt(float $ratePerPeriod, int $numberOfPeriods, float $presentValue): float
    {
        if ($numberOfPeriods <= 0) {
            return 0.0;
        }

        if (abs($ratePerPeriod) < 1e-12) {
            return -$presentValue / $numberOfPeriods;
        }

        $factor = pow(1 + $ratePerPeriod, $numberOfPeriods);

        return -($presentValue * $ratePerPeriod * $factor) / ($factor - 1);
    }

    private static function calculateAngsuranPokokBungaPerbulan(DataSimulasi $d): ?float
    {
        $p = $d->pelengkap;

        $plafond = self::asFloat(self::pick([$p?->plafond, $d->plafond], ''));
        $tenor = (int) (self::asFloat($d->tenor) ?? 0);
        $sukuBungaTahunan = (float) (self::asFloat(self::pick([$p?->suku_bunga], '0')) ?? 0.0);

        if ($plafond === null || $plafond <= 0 || $tenor <= 0 || $sukuBungaTahunan <= 0) {
            return null;
        }

        if ($sukuBungaTahunan > 1) {
            $sukuBungaTahunan /= 100;
        }

        return self::calculatePmt($sukuBungaTahunan / 12, $tenor, -$plafond);
    }

    private static function terbilang(mixed $value): string
    {
        if (function_exists('terbilang_id')) {
            return terbilang_id($value);
        }

        return 'Nol';
    }

    // ─── SPPK ────────────────────────────────────────────────────────────────

    public static function buildSppkData(DataSimulasi $d): array
    {
        $p = $d->pelengkap;

        $alamat = self::pick([$p?->alamat], '');
        $alamat2 = self::pick([$p?->alamat_2], '');
        $alamatLengkap = trim(trim($alamat) . ' ' . trim($alamat2));
        if ($alamatLengkap === '') {
            $alamatLengkap = self::pick([$alamat, $alamat2]);
        }

        $plafondRaw        = self::pick([$p?->plafond, $d->plafond], '');
        $provisiRaw        = self::pick([$p?->biaya_provisi, $p?->prosentase_provisi, $d->provisi], '');
        $administrasiRaw   = self::pick([$p?->biaya_administrasi_kredit, $p?->prosentase_administrasi, $d->administrasi], '');
        $asuransiRaw       = self::pick([$p?->asuransi_jiwa_kredit, $p?->asuransi, $d->asuransi], '');
        $materaiRaw        = self::pick([$p?->materai], '80000');
        $biayaFlaggingRaw  = self::pick([$p?->biaya_flagging], '');
        $totalBiayaRaw     = self::toNumeric($administrasiRaw)
            + self::toNumeric($asuransiRaw)
            + self::toNumeric($materaiRaw)
            + self::toNumeric($biayaFlaggingRaw)
            + self::toNumeric($provisiRaw);
        $angsuranDimukaRaw = self::pick([$p?->angsuran_dibayar_dimuka, $d->amount_blokir_angsuran], '');
        $totalPenerimaanRaw = self::pick([$p?->total_penerimaan, $d->terima_bersih], '');
        $angsuranPerbulanPmt = self::calculateAngsuranPokokBungaPerbulan($d);
        $angsuranPerbulanRaw = self::pick([
            is_numeric($angsuranPerbulanPmt) ? (string) round($angsuranPerbulanPmt) : null,
            $p?->angsuran_pokok_bunga_perbulan,
            $d->angsuran,
        ], '');
        $biayaAdmAngsuranCalculated = null;
        if (trim((string) $angsuranDimukaRaw) !== '' && trim((string) $angsuranPerbulanRaw) !== '') {
            $biayaAdmAngsuranCalculated = max(0, self::toNumeric($angsuranDimukaRaw) - self::toNumeric($angsuranPerbulanRaw));
        }
        $biayaAdmAngsuranRaw = self::pick([
            is_numeric($biayaAdmAngsuranCalculated) ? (string) round($biayaAdmAngsuranCalculated) : null,
            $p?->biaya_administrasi_angsuran_perbulan_baa,
            $p?->prosentase_admin_bank,
            $d->biaya_adm_angs,
        ], '');

        $jangkaWaktu  = self::pick([$p?->jangka_waktu, $p?->jw, $d->tenor]);
        $tanggalSurat = self::pick([
            self::formatDateDisplay($p?->tgl_sppk),
            self::formatDateDisplay($d->tgl_permohonan),
            now()->format('d/m/Y'),
        ]);

        $rtRw = '_____ / _____';
        if ($p && (trim((string) $p->rt) !== '' || trim((string) $p->rw) !== '')) {
            $rtRw = trim((string) $p->rt) . ' / ' . trim((string) $p->rw);
        }

        $angsuranGabung = self::pick([$p?->angsuran_bank_baa], '');
        if ($angsuranGabung === '') {
            $angsuranGabung = self::formatAngsuranGabung($d);
        }

        return [
            'nomor_sppk'          => self::pick([$p?->no_sppk, $p?->no_pk], '......./SPPK/........./......../' . now()->format('Y')),
            'tanggal_surat'       => $tanggalSurat,
            'nama_debitur'        => self::pick([$p?->nama, $d->nama_debitur]),
            'no_ktp'              => self::pick([$p?->no_ktp]),
            'alamat'              => $alamatLengkap,
            'rt_rw'               => $rtRw,
            'desa_kel'            => self::pick([$p?->kel, $p?->cabang]),
            'kecamatan'           => self::pick([$p?->kec, $p?->cabang_kb]),
            'kota_kab'            => self::pick([$p?->kota_kab, $p?->dibuat_di, $p?->kota]),
            'desa_kab_kota'       => self::pick([$p?->kota_kab, $p?->dibuat_di, $p?->kota]),
            'kode_pos'            => self::pick([$p?->kode_pos]),

            'plafond_kredit'      => self::formatRp($plafondRaw),
            'jangka_waktu'        => $jangkaWaktu,
            'suku_bunga'          => self::pick([$p?->suku_bunga], '___'),
            'biaya_provisi'       => self::formatRp($provisiRaw),
            'biaya_administrasi'  => self::formatRp($administrasiRaw),
            'asuransi_jiwa'       => self::formatRp($asuransiRaw),
            'materai'             => self::formatRp($materaiRaw),
            'biaya_flagging'      => self::formatRp($biayaFlaggingRaw),
            'total_biaya'         => self::formatRp($totalBiayaRaw),
            'angsuran_dimuka'     => self::formatRp($angsuranDimukaRaw),
            'total_penerimaan'    => self::formatRp($totalPenerimaanRaw),
            'angsuran_perbulan'   => self::formatRp($angsuranPerbulanRaw),
            'biaya_adm_angsuran'  => self::formatRp($biayaAdmAngsuranRaw),
            'angsuran_terbilang'  => self::pick([
                $p?->terbilang_angsuran,
                self::terbilang($angsuranPerbulanRaw) . ' Rupiah',
            ]),

            'tgl_mulai'             => self::pick([
                self::formatDateDisplay($p?->tgl_awal_kredit),
                self::formatDateDisplay($d->tgl_permohonan),
            ]),
            'tgl_lunas'             => self::pick([
                self::formatDateDisplay($p?->tgl_akhir_kredit),
                self::formatDateDisplay($p?->tgl_akhir_kredit_penarikan),
                self::formatDateDisplay($d->tgl_lunas),
            ]),
            'angsuran_total_bulan'  => self::pick([$p?->kali_angsuran, $p?->jangka_waktu, $p?->jw, $d->tenor]),
            'angsuran_gabung'       => $angsuranGabung,
            'plafond_terbilang'     => self::pick([$p?->terbilang_penarikan], self::terbilang($plafondRaw) . ' Rupiah'),

            'no_sk_pensiun'         => self::pick([$p?->no_skep]),
            'tgl_sk_pensiun'        => self::pick([
                self::formatDateDisplay($p?->tgl_sppk),
                self::formatDateDisplay($p?->tanggal),
            ]),
            'tgl_surat_kuasa'       => self::pick([
                self::formatDateDisplay($p?->tgl_kuasa_potong_gaji),
                self::formatDateDisplay($p?->tgl_surat_pernyataan_kuasa_potong_gaji),
            ]),
            'nama_sk_pensiun'       => self::pick([$p?->atas_nama_sk, $p?->nama, $d->nama_debitur]),

            'kota_ttd'              => self::pick([$p?->dibuat_di, $p?->kota_kab, $p?->kota]),
            'hari_ttd'              => self::pick([$p?->hari]),
            'tgl_ttd'               => self::pick([
                self::formatDateDisplay($p?->tanggal),
                self::formatDateDisplay($p?->tanggal_pk),
                self::formatDateDisplay($p?->tgl_sppk),
                now()->format('d/m/Y'),
            ]),
            'nama_ttd_debitur'      => self::pick([$p?->nama, $d->nama_debitur]),
            'nama_kuasa_kb_bank'    => self::pick([$p?->nama_petugas_nbp, $p?->nama_perwakilan_kb]),
        ];
    }

    // ─── Perjanjian Kredit ───────────────────────────────────────────────────

    public static function buildPerjanjianKreditData(DataSimulasi $d): array
    {
        $p = $d->pelengkap;

        $alamat = self::pick([$p?->alamat], '');
        $alamat2 = self::pick([$p?->alamat_2], '');
        $alamatLengkap = trim(trim($alamat) . ' ' . trim($alamat2));
        if ($alamatLengkap === '') {
            $alamatLengkap = self::pick([$alamat, $alamat2]);
        }
        
        $plafondRaw          = self::pick([$p?->plafond, $d->plafond], '');
        $provisiRaw          = self::pick([$p?->biaya_provisi, $p?->prosentase_provisi, $d->provisi], '');
        $administrasiRaw     = self::pick([$p?->biaya_administrasi_kredit, $p?->prosentase_administrasi, $d->administrasi], '');
        $asuransiRaw         = self::pick([$p?->asuransi_jiwa_kredit, $p?->asuransi, $d->asuransi], '');
        $materaiRaw          = self::pick([$p?->materai], '80000');
        $biayaFlaggingRaw    = self::pick([$p?->biaya_flagging], '');
        $totalBiayaRaw       = self::pick([$p?->total_biaya, $d->total_biaya], '');
        $angsuranDimukaRaw   = self::pick([$p?->angsuran_dibayar_dimuka, $d->amount_blokir_angsuran], '');
        $totalPenerimaanRaw  = self::pick([$p?->total_penerimaan, $d->terima_bersih], '');
        $angsuranPerbulanRaw = self::pick([$p?->angsuran_pokok_bunga_perbulan, $d->angsuran], '');
        $biayaAdmAngsuranRaw = self::pick([$p?->biaya_administrasi_angsuran_perbulan_baa, $p?->prosentase_admin_bank, $d->biaya_adm_angs], '');

        $jangkaWaktu        = self::pick([$p?->jangka_waktu, $p?->jw, $d->tenor]);
        $tglMulai           = self::pick([
            self::formatDateDisplay($p?->tgl_awal_kredit),
            self::formatDateDisplay($d->tgl_permohonan),
        ]);
        $tglLunas           = self::pick([
            self::formatDateDisplay($p?->tgl_akhir_kredit),
            self::formatDateDisplay($p?->tgl_akhir_kredit_penarikan),
            self::formatDateDisplay($d->tgl_lunas),
        ]);
        $angsuranTotalBulan = self::pick([$p?->kali_angsuran, $p?->jangka_waktu, $p?->jw, $d->tenor]);

        $rtRw = '_____ / _____';
        if ($p && (trim((string) $p->rt) !== '' || trim((string) $p->rw) !== '')) {
            $rtRw = trim((string) $p->rt) . ' / ' . trim((string) $p->rw);
        }

        $angsuranGabung = self::pick([$p?->angsuran_bank_baa], '');
        if ($angsuranGabung === '') {
            $angsuranGabung = self::formatRp(self::toNumeric($angsuranPerbulanRaw) + self::toNumeric($biayaAdmAngsuranRaw));
        }

            $plafond = self::asFloat($d->plafond) ?? 0.0;
            $prosentaseProvisi =0.5;// (float) ($this->asNumeric($pelengkap?->prosentase_provisi));
            $prosentaseAdministrasi = 0.5; //(float) ($this->asNumeric($pelengkap?->prosentase_administrasi));
          
            $sukuBungaTahunan = self::asFloat($p?->suku_bunga) ?? 0.0;
            if ($sukuBungaTahunan > 1) {
                $sukuBungaTahunan /= 100;
            }

            $tenor = (int) (self::asFloat($d->tenor) ?? 0);
            $angsuranPmt = $tenor > 0 ? self::pmt($sukuBungaTahunan / 12, $tenor, $plafond) : 0.0;

            $flagging = 0; //(float) ($this->asNumeric($pelengkap?->biaya_flagging) ?? 0);
            if ($d->instansi === 'TASPEN') {
                $flagging = 816000.0;
            }
            if ($d->instansi === 'ASABRI') {
                $flagging = 250000.0;
            }

            $asuransiJiwa = self::toNumeric(self::pick([$p?->asuransi_jiwa_kredit, $d->asuransi], '0'));
            $materai = self::toNumeric(self::pick([$p?->materai], '80000'));
            $totalAngsuran = self::asFloat($d->total_angsuran) ?? 0.0;

            $totalBiayaBank = $plafond * ($prosentaseProvisi / 100) + $plafond * ($prosentaseAdministrasi / 100);
            $totalBiayaMitra = $asuransiJiwa + $flagging + $plafond * (5 / 100) + $materai;
            $totalPenerimaan = $plafond - $totalBiayaBank - $totalBiayaMitra;
        return [
            'no_pk'          => self::pick([$p?->no_pk], ''),
            // ── Identitas Debitur ────────────────────────────────────────────
            'nama_debitur'         => self::pick([$p?->nama, $d->nama_debitur]),
            'no_ktp'               => self::pick([$p?->no_ktp]),
            'alamat'               => $alamatLengkap,
            'rt_rw'                => $rtRw,
            'rt'                    => self::pick([$p?->rt]),
            'rw'                    => self::pick([$p?->rw]),
            'desa_kel'             => self::pick([$p?->kel, '']),
            'kecamatan'            => self::pick([$p?->kec, $p?->cabang_kb]),
            'kota_kab'             => self::pick([$p?->kota, '']),
            'kode_pos'             => self::pick([$p?->kode_pos]),

            // ── Pasal 1 – Fasilitas Kredit ───────────────────────────────────
            'plafond_kredit'       => self::formatRp($plafondRaw),
            'plafond_kredit_raw'   => self::toNumeric($plafondRaw),
            'jangka_waktu'         => $jangkaWaktu,
            'suku_bunga'           => self::pick([$p?->suku_bunga], '___'),
            'biaya_provisi'        => self::formatRp((self::toNumeric($provisiRaw) / 100) * self::toNumeric($plafondRaw)),
            'biaya_provisi_raw'    => self::toNumeric($provisiRaw),
            'biaya_administrasi'   => self::formatRp((self::toNumeric($administrasiRaw) / 100) * self::toNumeric($plafondRaw)),
            'biaya_administrasi_raw' => self::toNumeric($administrasiRaw),
            'asuransi_jiwa'        => self::formatRp($asuransiRaw),
            'materai'              => self::formatRp($materaiRaw),
            'biaya_flagging'       => self::formatRp($biayaFlaggingRaw),
            'total_biaya'          => self::formatRp($totalBiayaMitra),
            'angsuran_dimuka'      => self::formatRp($angsuranDimukaRaw),
            'total_penerimaan'     => self::formatRp($totalPenerimaan),
            'angsuran_perbulan'    => self::formatRp(floor($angsuranPmt)),
            'biaya_adm_angsuran'   => self::formatRp(max(0, floor(self::toNumeric($angsuranPerbulanRaw)) - floor($angsuranPmt))),

            // ── Pasal 2 – Jangka Waktu ───────────────────────────────────────
            'tgl_mulai'            => $tglMulai,
            'tgl_lunas'            => $tglLunas,
            'angsuran_total_bulan' => $angsuranTotalBulan,
            'angsuran_gabung'      => $angsuranGabung,
            'angsuran_terbilang'   => self::pick([
                $p?->terbilang_angsuran,
                self::terbilang($angsuranDimukaRaw) . ' Rupiah',
            ]),

            // ── Referensi SPPK ───────────────────────────────────────────────
            'nomor_sppk'           => self::pick([$p?->no_sppk, $p?->no_pk]),
            'tanggal_sppk'         => self::pick([self::formatDateDisplay($p?->tgl_sppk),
            ]),
            'nomor_substitusi_pic' => self::pick([$p?->nomor_substitusi_pic, $p?->no_surat_kuasa_substitusi]),
            'tanggal_substitusi_pic' => self::pick([
                self::formatDateDisplay($p?->tanggal_substitusi),
                self::formatDateDisplay($p?->tanggal_surat_kuasa_substitusi),
                self::formatDateDisplay($p?->tgl_sppk),
                self::formatDateDisplay($p?->tanggal_pk),
                self::formatDateDisplay($p?->tanggal),
            ]),

            // ── Pasal 3 – Penarikan ──────────────────────────────────────────
            'plafond_terbilang'    => self::pick([$p?->terbilang_penarikan], self::terbilang($plafondRaw) . ' Rupiah'),

            // ── Pasal 5 – Jaminan ────────────────────────────────────────────
            'no_sk_pensiun'        => self::pick([$p?->no_skep]),
            'nama_sk_pensiun'      => self::pick([$p?->atas_nama_sk, $p?->nama, $d->nama_debitur]),
            // ── Pejabat / AO ─────────────────────────────────────────────
            'nama_perwakilan_kb'   => self::pick([$p?->nama_perwakilan_kb]),
            'jabatan'              => self::pick([$p?->jabatan]),
            'nama_ao'              => self::pick([$p?->nama_ao]),
            'kode_ao'              => self::pick([$p?->kode_ao]),
            // ── Tanda Tangan ─────────────────────────────────────────────────
            'kota_ttd'             => self::pick([$p?->kota]),
            'hari_ttd'             => self::pick([self::getNamaHariIndonesia($p?->tanggal_pk)]),
            'tgl_ttd'              => self::pick([
                self::formatDateDisplay($p?->tanggal_pk),
                now()->format('d/m/Y'),
            ]),
            'nama_ttd_debitur'     => self::pick([$p?->nama, $d->nama_debitur]),
        ];
    }
    public static function getNamaHariIndonesia($tanggal) {
        // 1. Ubah string tanggal menjadi objek DateTime
        $date = new \DateTime($tanggal);
        
        // 2. Buat formatter dengan locale Indonesia (id_ID)
        $formatter = new \IntlDateFormatter(
            'id_ID',
            \IntlDateFormatter::FULL, // Format tanggal penuh
            \IntlDateFormatter::NONE, // Tidak menampilkan waktu
            'Asia/Jakarta',
            \IntlDateFormatter::GREGORIAN,
            'EEEE' // Pola 'EEEE' artinya kita hanya mengambil nama hari lengkap
        );
        
        // 3. Kembalikan hasil formatnya
        return $formatter->format($date);
    }

static function  PMT($rate, $nper, $pv, $fv = 0, $type = 0)
{

    
    if ($rate == 0) {
        return -($pv + $fv) / $nper;
    }

    $pow = pow(1 + $rate, $nper);

    $pmt = ($rate * ($fv + $pow * $pv)) / (($pow - 1) * (1 + $rate * $type));

    return $pmt;
}
static function  hitungAngsuranAnuitas($pokok, $bungaTahunan, $tenorBulan)
{
    // bunga per bulan (misal 12% / tahun = 1% per bulan)
    // $i = ($bungaTahunan*100 / 100) / 12;

    // // kalau bunga 0 (edge case)
    // if ($i == 0) {
    //     return $pokok / $tenorBulan;
    // }

    // rumus anuitas
    
    $i=$bungaTahunan*100;
   $angsuran = ($pokok*$bungaTahunan/12)/(1-pow(1+$bungaTahunan/12,-$tenorBulan)); // + 10000
   //c32 : plafon
   //c21 : rate
   //C29 : tenor

    //$angsuran = $pokok * ($i * pow(1 + $i, $tenorBulan)) / (pow(1 + $i, $tenorBulan) - 1);

    return $angsuran;
}    
    // ─── SI ─────────────────────────────────────────────────────────────────

    public static function buildSiTakeOverData(DataSimulasi $d): array
    {
        return self::buildSiData($d, 'take_over');
    }

    public static function buildSiNewTopupData(DataSimulasi $d): array
    {
        return self::buildSiData($d, 'new_topup');
    }

    private static function buildSiData(DataSimulasi $d, string $variant): array
    {
        $p = $d->pelengkap;

        $alamat = self::pick([$p?->alamat], '');
        $alamat2 = self::pick([$p?->alamat_2], '');
        $alamatLengkap = trim(trim($alamat) . ' ' . trim($alamat2));
        if ($alamatLengkap === '') {
            $alamatLengkap = self::pick([$alamat, $alamat2]);
        }

        $nomorRekening = self::pick([$p?->norek], '________________');
        $plafondRaw = self::pick([$p?->plafond, $d->plafond], '');
        $totalPencairanRaw = self::pick([$p?->total_penerimaan, $d->terima_bersih, $plafondRaw], '');
        $isTakeOver = $variant === 'take_over';

        return [
            'jenis_si' => $variant,
            'nomor_si' => self::pick([$p?->no_si, $p?->no, $p?->no_pk], '....../SI/....../'.now()->format('Y')),
            'perihal' => $isTakeOver
                ? 'Permohonan Pencairan Fasilitas Kredit Take Over'
                : 'Permohonan Pencairan Fasilitas Kredit New / Top Up',
            'tanggal_surat' => self::pick([
                self::formatDateDisplay($p?->tanggal),
                self::formatDateDisplay($p?->tanggal_pk),
                self::formatDateDisplay($p?->tgl_sppk),
                now()->format('d/m/Y'),
            ]),
            'tanggal_ttd' => self::pick([
                self::formatDateDisplay($p?->tanggal),
                self::formatDateDisplay($p?->tanggal_pk),
                self::formatDateDisplay($p?->tgl_sppk),
                now()->format('d/m/Y'),
            ]),
            'nama_debitur' => self::pick([$p?->nama, $d->nama_debitur]),
            'no_ktp' => self::pick([$p?->no_ktp]),
            'nomor_pensiun' => self::pick([$d->nomor_pensiun, $p?->no_si, $p?->no]),
            'instansi' => self::pick([$d->instansi, $p?->cabang, $p?->cabang_kb]),
            'alamat' => $alamatLengkap,
            'plafond' => self::formatRp($plafondRaw),
            'plafond_terbilang' => self::pick([$p?->terbilang_penarikan], '(' . self::terbilang($plafondRaw) . ')'),
            'jumlah_debitur' => self::pick([$p?->jumlah_debitur], '1'),
            'produk' => self::pick([$p?->produk], 'Kredit Pensiun Reguler dan/ Kredit Pensiun Platinum'),
            'jenis_kredit_label' => $isTakeOver ? 'Take Over' : 'New / Top Up',
            'total_pencairan' => self::formatRp($totalPencairanRaw),
            'rekening_penerima' => $nomorRekening,
            'rekening_escrow_no' => self::pick([$p?->rekening_escrow_no], '10000003184'),
            'rekening_flagging_no' => self::pick([$p?->rekening_flagging_no], '10000003185'),
            'rekening_asuransi_no' => self::pick([$p?->rekening_asuransi_no], '1002147447'),
            'rekening_ksp_nama' => 'KSP Nata Buana Pasundan',
            'nama_asuransi' => 'PT Heksa Solution Insurance',
            'nama_ketua_koperasi' => self::pick([$p?->nama_ketua_koperasi, $p?->nama_petugas_nbp, $p?->nama_perwakilan_kb, $p?->nama, $d->nama_debitur], '(Nama Ketua Koperasi)'),
            'attention_name' => self::pick([$p?->up_pic_kb], 'Ibu Rahel Febrina'),
            'nomor_pks_nbp' => self::pick([$p?->nomor_pks_nbp], '040/PKS-NBP/I/2026'),
            'nomor_pks_kb' => self::pick([$p?->nomor_pks_kb], 'PKS.001/CPA II/I/2026'),
            'tanggal_pks' => self::pick([$p?->tanggal_pks], '02 Januari 2026'),
            'nama_perwakilan_kb' => self::pick([$p?->nama_perwakilan_kb, $p?->nama_petugas_nbp]),
            'jabatan' => self::pick([$p?->jabatan, $p?->jabatan_petugas]),
            'nama_kuasa_kb_bank' => self::pick([$p?->nama_petugas_nbp, $p?->nama_perwakilan_kb]),
            'kota_ttd' => self::pick([$p?->dibuat_di, $p?->kota_kab, $p?->kota]),
            'hari_ttd' => self::pick([$p?->hari], now()->locale('id')->translatedFormat('l')),
        ];
    }
}
