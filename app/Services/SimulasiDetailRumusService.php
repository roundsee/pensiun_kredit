<?php

namespace App\Services;

class SimulasiDetailRumusService
{
    public const IURAN_TETAP = 10000.0;

    public const MATERAI = 80000.0;

    /**
     * Susun rincian asal-usul nilai simulasi (komponen, nilai, rumus).
     *
     * @param  array<string, mixed>  $data
     * @return array<int, array{komponen: string, nilai: float, rumus: string}>
     */
    public function build(array $data): array
    {
        $plafond = $this->num($data, 'plafond');
        $tenor = (int) $this->num($data, 'tenor');
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

        return $rows;
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
