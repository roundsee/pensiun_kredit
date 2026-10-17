<?php

namespace App\Services;

use App\Models\ProductStruct;
use Carbon\Carbon;

class SimulasiDetailRumusService
{
    public const IURAN_TETAP = 10000.0;

    public const MATERAI = 80000.0;

    public const SISA_GAJI_MINIMAL = 110000.0;

    /** @var array<string, ProductStruct|null> */
    private array $structCache = [];

    public function __construct(private readonly KbSimulationExcelService $simulationService) {}

    /**
     * Susun rincian asal-usul nilai simulasi (komponen, nilai, rumus).
     *
     * @param  array<string, mixed>  $data
     * @return array<int, array{komponen: string, nilai: float, nilai_text?: string, rumus: string}>
     */
    public function build(array $data): array
    {
        $plafond = $this->num($data, 'plafond');
        $tenor = (int) $this->num($data, 'tenor');
        $tenorMax = (int) $this->num($data, 'tenor_max');
        $plafondMax = $this->num($data, 'plafond_max');
        $angsuran = $this->num($data, 'angsuran');
        $biayaAdmAngs = $this->num($data, 'biaya_adm_angs');
        $totalAngsuran = $this->num($data, 'total_angsuran');
        $dataMaintenance = $this->num($data, 'data_maintenance');
        $provisi = $this->num($data, 'provisi');
        $administrasi = $this->num($data, 'administrasi');
        $asuransi = $this->num($data, 'asuransi');
        $extraPremi = $this->num($data, 'extra_premi');
        $blokirCount = (int) $this->num($data, 'blokir_angsuran');
        $blokirAmount = $this->num($data, 'amount_blokir_angsuran');
        $pelunasan = $this->num($data, 'pelunasan');
        $simpananPokok = $this->num($data, 'simpanan_pokok');
        $tataLaksana = $this->num($data, 'tata_laksana');
        $extTataLaksana = $this->num($data, 'ext_tatalaksana');
        $totalBiaya = $this->num($data, 'total_biaya');
        $sisaGajiSaatPengajuan = $this->num($data, 'sisa_gaji_saat_pengajuan');
        $sisaGajiAkhir = $this->num($data, 'sisa_gaji_akhir');
        $terimaBersih = $this->num($data, 'terima_bersih');

        $pmt = max(0.0, $angsuran - self::IURAN_TETAP);
        $admPercent = $angsuran > 0
            ? round((($biayaAdmAngs - $dataMaintenance) / $angsuran) * 100, 4)
            : 0.0;
        $provisiPercent = $plafond > 0 ? round(($provisi / $plafond) * 100, 4) : 0.0;
        $administrasiPercent = $plafond > 0 ? round(($administrasi / $plafond) * 100, 4) : 0.0;
        $asuransiPercent = $plafond > 0 ? round(($asuransi / $plafond) * 100, 4) : 0.0;

        $rows = [];

        $rows[] = [
            'komponen' => 'Angsuran',
            'nilai' => $angsuran,
            'rumus' => $tenor > 0 && $plafond > 0
                ? 'Pokok angsuran (anuitas) '.$this->rp($pmt).' + iuran tetap '.$this->rp(self::IURAN_TETAP)
                : '-',
        ];

        $rows[] = [
            'komponen' => 'Adm Angsuran',
            'nilai' => $biayaAdmAngs,
            'rumus' => 'Angsuran ('.$this->rp($angsuran).') × adm_proc ('.$this->pct($admPercent).') + data_maintenance ('.$this->rp($dataMaintenance).')',
        ];

        $rows[] = [
            'komponen' => 'Total Angsuran',
            'nilai' => $totalAngsuran,
            'rumus' => 'Angsuran ('.$this->rp($angsuran).') + Adm Angsuran ('.$this->rp($biayaAdmAngs).')',
        ];

        $rows[] = [
            'komponen' => 'Provisi',
            'nilai' => $provisi,
            'rumus' => 'Plafond ('.$this->rp($plafond).') × '.$this->pct($provisiPercent),
        ];

        $rows[] = [
            'komponen' => 'Administrasi',
            'nilai' => $administrasi,
            'rumus' => 'Plafond ('.$this->rp($plafond).') × '.$this->pct($administrasiPercent),
        ];

        $rows[] = [
            'komponen' => 'Asuransi',
            'nilai' => $asuransi,
            'rumus' => 'Plafond ('.$this->rp($plafond).') × '.$this->pct($asuransiPercent),
        ];

        if ($extraPremi > 0) {
            $rows[] = [
                'komponen' => 'Extra Premi',
                'nilai' => $extraPremi,
                'rumus' => 'Biaya tambahan premi',
            ];
        }

        $rows[] = [
            'komponen' => 'Blokir Angsuran',
            'nilai' => $blokirAmount,
            'rumus' => $blokirCount.' × Total Angsuran ('.$this->rp($totalAngsuran).')',
        ];

        $rows[] = [
            'komponen' => 'Tata Laksana',
            'nilai' => $tataLaksana,
            'rumus' => 'Plafond × tarif tata laksana + flagging (Taspen/Asabri) + materai ('.$this->rp(self::MATERAI).')'.($extTataLaksana > 0 ? ' + ext tata laksana ('.$this->rp($extTataLaksana).')' : ''),
        ];

        if ($pelunasan > 0) {
            $rows[] = [
                'komponen' => 'Pelunasan',
                'nilai' => $pelunasan,
                'rumus' => 'Nilai pelunasan yang diinput',
            ];
        }

        if ($simpananPokok > 0) {
            $rows[] = [
                'komponen' => 'Simpanan Pokok',
                'nilai' => $simpananPokok,
                'rumus' => 'Nilai simpanan pokok yang diinput',
            ];
        }

        $rows[] = [
            'komponen' => 'Total Biaya',
            'nilai' => $totalBiaya,
            'rumus' => 'Provisi + Administrasi + Asuransi + Blokir Angsuran + Tata Laksana + Pelunasan + Simpanan Pokok',
        ];

        $rows[] = [
            'komponen' => 'Sisa Gaji Akhir',
            'nilai' => $sisaGajiAkhir,
            'rumus' => 'Sisa gaji saat pengajuan ('.$this->rp($sisaGajiSaatPengajuan).') − Total Angsuran ('.$this->rp($totalAngsuran).')',
        ];

        $rows[] = [
            'komponen' => 'Terima Bersih',
            'nilai' => $terimaBersih,
            'rumus' => 'Plafond ('.$this->rp($plafond).') − Total Biaya ('.$this->rp($totalBiaya).')',
        ];

        $struct = $this->resolveStruct($data);
        $usiaMaxProduct = (int) ($struct->usia_max ?? 0);
        $tenorMaxProduct = (int) ($struct->tenor_max ?? 0);
        $plafondMaxProduct = (float) ($struct->plafond_max ?? 0.0);
        $dbrPercent = $this->toPercentValue((float) ($struct->dbr_percent ?? 0.0));
        $sisaGaji = $sisaGajiSaatPengajuan > 0
            ? $sisaGajiSaatPengajuan
            : max(0.0, $this->num($data, 'gaji_pensiun') - $this->num($data, 'angsuran_lain'));
        $tanggalSimulasi = $data['tgl_permohonan'] ?? $data['tanggal_simulasi'] ?? null;

        $rows[] = [
            'komponen' => 'Tenor Max',
            'nilai' => $tenorMax,
            'nilai_text' => $tenorMax > 0 ? number_format($tenorMax, 0, ',', '.').' bulan' : '-',
            'rumus' => sprintf(
                'Batas tenor dari sisa masa (%s) s/d (%d) usia_max, dibatasi tenor_max (%d) product struct',
                $this->ageText($data['tanggal_lahir'] ?? null, $tanggalSimulasi),
                $usiaMaxProduct,
                $tenorMaxProduct
            ),
        ];

        $rows[] = [
            'komponen' => 'Plafond Max',
            'nilai' => $plafondMax,
            'nilai_text' => $this->rp($plafondMax),
            'rumus' => sprintf(
                'PV dari basis angsuran (DBR (%s x %s) / sisa gaji (%s)) pada tenor, dibatasi plafond_max (%s) product struct',
                $this->pct($dbrPercent),
                $this->rp($sisaGaji),
                $this->rp(self::SISA_GAJI_MINIMAL),
                $this->rp($plafondMaxProduct)
            ),
        ];

        $rows[] = [
            'komponen' => 'Usia Lunas',
            'nilai' => $this->num($data, 'usia_lunas'),
            'nilai_text' => $this->usiaLunasText($data),
            'rumus' => sprintf(
                'Usia pada tanggal lunas (tgl_permohonan (%s) + tenor (%d))',
                $tanggalSimulasi !== null ? Carbon::parse($tanggalSimulasi)->toDateString() : '-',
                $tenor
            ),
        ];

        return $rows;
    }

    /**
     * Cek apakah rincian tersimpan belum memuat komponen terbaru (Tenor Max,
     * Plafond Max, Usia Lunas) sehingga perlu disusun ulang saat ditampilkan.
     *
     * @param  array<int, mixed>  $detail
     */
    public function isOutdated(array $detail): bool
    {
        if (empty($detail)) {
            return true;
        }

        $komponen = array_map(
            static fn ($row) => is_array($row) ? ($row['komponen'] ?? null) : null,
            $detail
        );

        foreach (['Tenor Max', 'Plafond Max', 'Usia Lunas'] as $required) {
            if (! in_array($required, $komponen, true)) {
                return true;
            }
        }

        return false;
    }

    private function resolveStruct(array $data): ?ProductStruct
    {
        $bankTujuan = (string) ($data['bank_tujuan'] ?? '');
        $produk = (string) ($data['produk'] ?? '');
        $jenisPensiun = (string) ($data['jenis_pensiun'] ?? '');

        if ($bankTujuan === '' && $produk === '') {
            return null;
        }

        $cacheKey = strtolower($bankTujuan.'|'.$produk.'|'.$jenisPensiun);

        if (! array_key_exists($cacheKey, $this->structCache)) {
            $this->structCache[$cacheKey] = $this->simulationService->resolveProductStruct($bankTujuan, $produk, $jenisPensiun);
        }

        return $this->structCache[$cacheKey];
    }

    private function ageText(mixed $tanggalLahir, mixed $tanggalAcuan): string
    {
        if (empty($tanggalLahir) || empty($tanggalAcuan)) {
            return '-';
        }

        $diff = rescue(function () use ($tanggalLahir, $tanggalAcuan) {
            return Carbon::parse($tanggalLahir)->diff(Carbon::parse($tanggalAcuan));
        }, null, false);

        if ($diff === null) {
            return '-';
        }

        return sprintf('%d thn %d bln', $diff->y, $diff->m);
    }

    private function toPercentValue(float $value): float
    {
        return $value > 0 && $value <= 1 ? $value * 100 : $value;
    }

    private function usiaLunasText(array $data): string
    {
        $text = trim((string) ($data['usia_lunas_text'] ?? ''));
        if ($text !== '' && $text !== '-') {
            return $text;
        }

        $tanggalLahir = $data['tanggal_lahir'] ?? null;
        $tanggalLunas = $data['tgl_lunas'] ?? null;

        if (! empty($tanggalLahir) && ! empty($tanggalLunas)) {
            $diff = rescue(function () use ($tanggalLahir, $tanggalLunas) {
                return Carbon::parse($tanggalLahir)->diff(Carbon::parse($tanggalLunas));
            }, null, false);

            if ($diff !== null) {
                return sprintf('%d thn %d bln', $diff->y, $diff->m);
            }
        }

        $usia = $this->num($data, 'usia_lunas');

        return $usia > 0 ? number_format($usia, 0, ',', '.').' thn' : '-';
    }

    private function num(array $data, string $key): float
    {
        $value = $data[$key] ?? 0;

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function rp(float $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    private function pct(float $value): string
    {
        $formatted = rtrim(rtrim(number_format($value, 4, ',', '.'), '0'), ',');

        return $formatted.'%';
    }
}
