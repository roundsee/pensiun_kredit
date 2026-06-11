<?php

namespace App\Http\Controllers;

use App\Models\DataSimulasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class DnkaController extends Controller
{
    public function downloadHorizontalTemplate(): BinaryFileResponse
    {
        $dataSimulasi = $this->resolveDataSimulasi();

        return $this->generateDnkaFromTemplate(
            templateFileName: 'DNKA_Horizontal.xlsx',
            downloadPrefix: 'DNKA_Horizontal',
            dataSimulasi: $dataSimulasi,
            isVerticalTemplate: false
        );
    }

    public function downloadVerticalTemplate(): BinaryFileResponse
    {
        $dataSimulasi = $this->resolveDataSimulasi();

        return $this->generateDnkaFromTemplate(
            templateFileName: 'DNKA_vertical.xlsx',
            downloadPrefix: 'DNKA_vertical',
            dataSimulasi: $dataSimulasi,
            isVerticalTemplate: true
        );
    }

    public function downloadDatanominatifTemplate(): BinaryFileResponse
    {
        $dataSimulasi = $this->resolveDataSimulasi();

        return $this->generateDocumentFromTemplate(
            templateFileName: 'DATA_NOMINATIF.xlsx',
            downloadPrefix: 'datanominatif',
            cellValues: $this->buildDatanominatifCellValues($dataSimulasi),
            documentLabel: 'Data Nominatif'
        );
    }

    public function downloadDataLosBulkTemplate(): BinaryFileResponse
    {
        $dataSimulasi = $this->resolveDataSimulasi(requireExplicitId: true);

        return $this->generateDocumentFromTemplate(
            templateFileName: 'Data_Los_bulk.xlsx',
            downloadPrefix: 'Data_Los_bulk',
            cellValues: $this->buildDataLosBulkCellValues($dataSimulasi),
            documentLabel: 'Data LOS Bulk',
            forceCellOverrides: $this->buildDataLosBulkForceOverrideCells(),
            applyToAllWorksheets: true
        );
    }

    public function downloadDataRekeningTemplate(): BinaryFileResponse
    {
        $dataSimulasi = $this->resolveDataSimulasi();

        return $this->generateDataRekeningByHeaderTemplate($dataSimulasi);
    }

    public function downloadRepaymentScheduleTemplate(): BinaryFileResponse
    {
        $dataSimulasi = $this->resolveDataSimulasi();

        return $this->generateDocumentFromTemplate(
            templateFileName: 'Repayment_Schedule.xlsx',
            downloadPrefix: 'Repayment_Schedule',
            cellValues: $this->buildRepaymentScheduleCellValues($dataSimulasi),
            documentLabel: 'Repayment Schedule'
        );
    }

    public function downloadPermohonanCifTemplate(): BinaryFileResponse
    {
        $dataSimulasi = $this->resolveDataSimulasi();

        return $this->generatePermohonanCifByHeaderTemplate($dataSimulasi);
    }

    public function downloadPelunasanToKbTemplate(): BinaryFileResponse
    {
        $dataSimulasi = $this->resolveDataSimulasi();

        return $this->generatePelunasanToKbByHeaderTemplate($dataSimulasi);
    }

    private function resolveDataSimulasi(bool $requireExplicitId = false): DataSimulasi
    {
        $dataSimulasiId = Request::input('data_simulasi_id');

        if ($requireExplicitId && !$dataSimulasiId) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Parameter data_simulasi_id wajib diisi.');
        }

        $query = DataSimulasi::query()->with('pelengkap');

        $dataSimulasi = $dataSimulasiId
            ? $query->find($dataSimulasiId)
            : $query->latest('id')->first();

        if (!$dataSimulasi) {
            abort(Response::HTTP_NOT_FOUND, 'Data simulasi tidak ditemukan.');
        }

        return $dataSimulasi;
    }

    private function generateDnkaFromTemplate(
        string $templateFileName,
        string $downloadPrefix,
        DataSimulasi $dataSimulasi,
        bool $isVerticalTemplate
    ): BinaryFileResponse {
        $cellValues = $isVerticalTemplate
            ? $this->buildVerticalCellValues($dataSimulasi)
            : $this->buildHorizontalCellValues($dataSimulasi);

        $forceCellOverrides = $isVerticalTemplate
            ? ['I17', 'I18']
            : [];

        return $this->generateDocumentFromTemplate(
            templateFileName: $templateFileName,
            downloadPrefix: $downloadPrefix,
            cellValues: $cellValues,
            documentLabel: 'DNKA',
            forceCellOverrides: $forceCellOverrides
        );
    }

    private function generateDocumentFromTemplate(
        string $templateFileName,
        string $downloadPrefix,
        array $cellValues,
        string $documentLabel,
        array $forceCellOverrides = [],
        bool $applyToAllWorksheets = false
    ): BinaryFileResponse {
        $templatePath = storage_path('upload/' . $templateFileName);

        if (!file_exists($templatePath)) {
            abort(Response::HTTP_NOT_FOUND, 'Template tidak ditemukan: ' . $templateFileName);
        }

        $downloadName = $downloadPrefix . '_' . now()->format('Ymd_His') . '.xlsx';
        $tempDir = storage_path('app/temp');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . $downloadName;

        if (!copy($templatePath, $tempPath)) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal menyalin template ' . $documentLabel . '.');
        }

        // Always allow overwrite on mapped cells; explicit overrides are additive.
        $effectiveForceCellOverrides = array_values(array_unique([
            ...array_keys($cellValues),
            ...$forceCellOverrides,
        ]));

        $this->applyCellValuesToSheetXml($tempPath, $cellValues, $effectiveForceCellOverrides, $applyToAllWorksheets);

        return response()->download(
            $tempPath,
            $downloadName,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }

    private function buildHorizontalCellValues(DataSimulasi $dataSimulasi): array
    {
        // Horizontal DNKA is a transpose of Vertical DNKA values.
        $verticalValues = $this->buildVerticalCellValues($dataSimulasi);

        $transposeMap = [
            'D3' => 'C3',
            'I6' => 'C7',
            'I7' => 'D7',
            'I8' => 'E7',
            'I9' => 'F7',
            'I10' => 'G7',
            'I11' => 'H7',
            'I12' => 'I7',
            'I13' => 'J7',
            'I14' => 'K7',
            'I15' => 'L7',
            'I16' => 'M7',
            'I17' => 'N7',
            'I18' => 'O7',
            'I19' => 'P7',
            'I20' => 'Q7',
            'I21' => 'R7',
            'I22' => 'S7',
            'I23' => 'T7',
            'I24' => 'U7',
            'I25' => 'V7',
            'I26' => 'W7',
            'I27' => 'X7',
            'I28' => 'Y7',
            'I29' => 'Z7',
            'I30' => 'AA7',
            'I31' => 'AB7',
            'I32' => 'AC7',
            'I33' => 'AD7',
            'I34' => 'AE7',
            'I35' => 'AF7',
            'I36' => 'AG7',
        ];

        $horizontalValues = [];
        foreach ($transposeMap as $verticalCell => $horizontalCell) {
            if (array_key_exists($verticalCell, $verticalValues)) {
                $horizontalValues[$horizontalCell] = $verticalValues[$verticalCell];
            }
        }

        return $horizontalValues;
    }

    private function buildDatanominatifCellValues(DataSimulasi $dataSimulasi): array
    {
        $pelengkap = $dataSimulasi->pelengkap;

        $biayaProvisi = $this->asNumeric($this->firstFilled($pelengkap?->biaya_provisi, $dataSimulasi->provisi));
        $biayaAdministrasiBank = $this->asNumeric($this->firstFilled($pelengkap?->biaya_administrasi_kredit, $dataSimulasi->administrasi));

        $totalBiayaBank = null;
        if (is_numeric($biayaProvisi) || is_numeric($biayaAdministrasiBank)) {
            $totalBiayaBank = (float) ($biayaProvisi ?? 0) + (float) ($biayaAdministrasiBank ?? 0);
        }

        return array_filter([
            'B7' => 'Tanggal ' . now()->translatedFormat('d F Y'),
            'H10' => $this->firstFilled($pelengkap?->nama, $dataSimulasi->nama_debitur),
            'H11' => $pelengkap?->no_ktp,
            'H12' => $this->formatDate($dataSimulasi->tanggal_lahir),
            'H13' => $pelengkap?->no_hp,
            'H14' => $dataSimulasi->nomor_pensiun,
            'H15' => $pelengkap?->no_skep,
            'H16' => $dataSimulasi->instansi,
            'H17' => $this->firstFilled($pelengkap?->no_pk, $pelengkap?->no_sppk, $pelengkap?->no),
            'H18' => $this->firstFilled($pelengkap?->tanggal_pk, $pelengkap?->tanggal, $pelengkap?->tgl_sppk),
            'H19' => $this->asNumeric($this->firstFilled($pelengkap?->plafond, $dataSimulasi->plafond)),
            'H20' => $this->firstFilled($pelengkap?->jangka_waktu, $pelengkap?->jw, $dataSimulasi->tenor) . ' Bulan',
            'H21' => $biayaProvisi,
            'H22' => $biayaAdministrasiBank,
            'H23' => $totalBiayaBank,
            'H24' => $this->asNumeric($this->firstFilled($dataSimulasi->administrasi, $pelengkap?->prosentase_administrasi)),
            'H25' => $this->asNumeric($this->firstFilled($pelengkap?->asuransi_jiwa_kredit, $dataSimulasi->asuransi)),
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function buildVerticalCellValues(DataSimulasi $dataSimulasi): array
    {
        try {
            $pelengkap = $dataSimulasi->pelengkap;

            $plafond = (float) ($this->asNumeric($dataSimulasi->plafond)) ;
            $prosentaseProvisi =0.5;// (float) ($this->asNumeric($pelengkap?->prosentase_provisi));
            $prosentaseAdministrasi = 0.5; //(float) ($this->asNumeric($pelengkap?->prosentase_administrasi));
            Log::info('Calculating vertical cell values', [
                'data_simulasi_id' => $dataSimulasi->id,
                'plafond' => $plafond,
                'prosentase_provisi' => $prosentaseProvisi,
                'prosentase_administrasi' => $prosentaseAdministrasi,
            ]);
            $sukuBungaTahunan = (float) ($this->asNumeric($this->firstFilled($pelengkap?->suku_bunga, 0)) ?? 0);
            if ($sukuBungaTahunan > 1) {
                $sukuBungaTahunan /= 100;
            }

            $tenor = $this->asNumeric($dataSimulasi->tenor);
            $angsuranPmt = $this->pmt($sukuBungaTahunan / 12, $tenor, $plafond);

            $flagging = (float) ($this->asNumeric($pelengkap?->biaya_flagging) ?? 0);
            if ($dataSimulasi->instansi === 'TASPEN') {
                $flagging = 816000.0;
            }
            if ($dataSimulasi->instansi === 'ASABRI') {
                $flagging = 250000.0;
            }

            $asuransiJiwa = (float) ($this->asNumeric($this->firstFilled($pelengkap?->asuransi_jiwa_kredit, $dataSimulasi->asuransi)) ?? 0);
            $materai = (float) ($this->asNumeric($pelengkap->materai) ?? 0);
            $totalAngsuran = (float) ($this->asNumeric($dataSimulasi->total_angsuran) ?? 0);

            $totalBiayaBank = $plafond * ($prosentaseProvisi / 100) + $plafond * ($prosentaseAdministrasi / 100);
            $totalBiayaMitra = $asuransiJiwa + $flagging + $plafond * (5 / 100) + $materai;

            $fieldValues = [
                'D3' => now()->translatedFormat('d F Y'),
                'I6' => $this->firstFilled($pelengkap?->nama, $dataSimulasi->nama_debitur),
                'I7' => $pelengkap?->no_ktp,
                'I8' => $this->formatDateSlash($dataSimulasi->tanggal_lahir),
                'I9' => $pelengkap?->no_hp,
                'I10' => $dataSimulasi->nomor_pensiun,
                'I11' => $pelengkap?->no_skep,
                'I12' => $dataSimulasi->instansi,
                'I13' => $this->firstFilled($pelengkap?->no_pk),
                'I14' => $this->firstFilled($pelengkap?->tanggal_pk),
                'I15' => $dataSimulasi->tenor,
                'I16' => $this->asNumeric($dataSimulasi->plafond),
                'I17' => $plafond * ($prosentaseProvisi / 100),
                'I18' => $plafond * ($prosentaseAdministrasi / 100),
                'I19' => $totalBiayaBank,
                'I20' => $asuransiJiwa,
                'I21' => $flagging,
                'I22' => $plafond * (5 / 100),
                'I23' => $materai,
                'I24' => $totalBiayaMitra,
                'I25' => $totalAngsuran,
                'I26' => $plafond - $totalBiayaMitra - $totalBiayaBank - $totalAngsuran,
                'I27' => $angsuranPmt,
                'I28' => $totalAngsuran - $angsuranPmt,
                'I29' => $totalAngsuran,
                'I30' => $pelengkap?->norek,
                'I31' => $pelengkap?->cabang_kb,
                'I32' => $pelengkap?->nama_ao,
                'I33' => $pelengkap?->kode_ao,
                'I34' => $pelengkap?->ket,
                'I35' => $pelengkap?->kantor_bayar,
                'I36' => $this->asNumeric($dataSimulasi->pelunasan),
            ];

            return array_filter($fieldValues, fn ($value) => $value !== null && $value !== '');
        } catch (\Throwable $e) {
            Log::error('DNKA vertical generation failed', [
                'data_simulasi_id' => $dataSimulasi->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function calculatePmt(float $ratePerPeriod, int $numberOfPeriods, float $presentValue): float
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

    private function resolveVerticalField(string $cellRef, callable $resolver): mixed
    {
        try {
            return $resolver();
        } catch (\Throwable $e) {
            $message = 'DNKA vertical failed at ' . $cellRef . ': ' . $e->getMessage();
            Log::error($message);
            error_log($message);

            throw new \RuntimeException($message, 0, $e);
        }
    }

    private function buildDataLosBulkCellValues(DataSimulasi $dataSimulasi): array
    {
        $pelengkap = $dataSimulasi->pelengkap;

        $namaDebitur = (string) ($dataSimulasi->nama_debitur ?? '');
        $noKtpDebitur = (string) ($pelengkap?->no_ktp ?? '');
        $tanggalLahirDebitur = (string) ($this->formatDate($dataSimulasi->tanggal_lahir) ?? '');
        $namaPasangan = (string) ($pelengkap?->nama_pasangan ?? '');
        $ktpPasangan = (string) ($pelengkap?->ktp_pasangan ?? '');
        $tanggalLahirPasangan = (string) ($this->formatDate($pelengkap?->tgl_lahir_pasangan) ?? '');
        $plafond = $dataSimulasi->plafond ?? '';
        $tenor = $dataSimulasi->tenor ?? '';

        return [
            // Data LOS Bulk now writes only to row 2.
            'C2' => $namaDebitur,
            'D2' => $noKtpDebitur,
            'E2' => $tanggalLahirDebitur,
            'F2' => $namaPasangan,
            'G2' => $ktpPasangan,
            'H2' => $tanggalLahirPasangan,
            'I2' => 'IDR',
            'J2' => $plafond,
            'K2' => $tenor,
            'L2' => 10,
        ];
    }

    private function buildDataLosBulkForceOverrideCells(): array
    {
        return [
            'C2', 'D2', 'E2', 'F2', 'G2', 'H2', 'I2', 'J2', 'K2', 'L2',
        ];
    }

    private function generateDataRekeningByHeaderTemplate(DataSimulasi $dataSimulasi): BinaryFileResponse
    {
        $templateFileName = 'Data_rekening.xlsx';
        $templatePath = storage_path('upload/' . $templateFileName);

        if (!file_exists($templatePath)) {
            abort(Response::HTTP_NOT_FOUND, 'Template tidak ditemukan: ' . $templateFileName);
        }

        $downloadName = 'Data_rekening_' . now()->format('Ymd_His') . '.xlsx';
        $tempDir = storage_path('app/temp');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . $downloadName;

        if (!copy($templatePath, $tempPath)) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal menyalin template Data Rekening.');
        }

        $headerData = $this->extractHeaderCellMap($tempPath);
        $targetRow = ($headerData['header_row'] ?? 1) + 1;
        $cellValues = $this->buildDataRekeningHeaderMappedCellValues(
            $headerData['header_map'] ?? [],
            $targetRow,
            $dataSimulasi
        );

        $this->applyCellValuesToSheetXml($tempPath, $cellValues, array_keys($cellValues));

        return response()->download(
            $tempPath,
            $downloadName,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }

    private function buildDataRekeningHeaderMappedCellValues(array $headerMap, int $targetRow, DataSimulasi $dataSimulasi): array
    {
        $pelengkap = $dataSimulasi->pelengkap;

        $tanggalAkadAwal = $this->formatDateCompact(
            $this->firstFilled($pelengkap?->tanggal_pk, $pelengkap?->tgl_sppk, $pelengkap?->tanggal)
        );
        $tanggalAkadAkhir = $this->formatDateCompact(
            $this->firstFilled($pelengkap?->tgl_akhir_kredit, $pelengkap?->tgl_akhir_kredit_penarikan, $dataSimulasi->tgl_lunas)
        );

        $angsuran = (float) ($this->asNumeric($dataSimulasi->angsuran) ?? 0);
        $adminAngsuran = (float) ($this->asNumeric(
            $this->firstFilled($pelengkap?->biaya_administrasi_angsuran_perbulan_baa, $dataSimulasi->biaya_adm_angs)
        ) ?? 0);
        $kewajibanPerBulan = $angsuran + $adminAngsuran;

        $cellValues = [];

        foreach ($headerMap as $column => $header) {
            $value = match (true) {
                $header === 'no' => 1,
                $header === 'namadebitur' => $this->firstFilled($pelengkap?->nama, $dataSimulasi->nama_debitur, ''),
                $header === 'nopensiun' => $this->firstFilled($dataSimulasi->nomor_pensiun, ''),
                $header === 'pengelolapensiun' => $this->firstFilled($dataSimulasi->instansi, ''),
                $header === 'nomorpksesuaiformat' => $this->firstFilled($pelengkap?->no_pk, $pelengkap?->no_sppk, $pelengkap?->no, ''),
                $header === 'tanggalakadawalsesuaiformat' => $tanggalAkadAwal,
                $header === 'tanggalakadakhirsesuaiformat' => $tanggalAkadAkhir,
                $header === 'tanggalpembayarankewajiban' => 24,
                $header === 'plafondkredit' => $this->asNumeric($dataSimulasi->plafond),
                $header === 'tenor' => $dataSimulasi->tenor,
                $header === 'sukubungakredit' => 0.1,
                $header === 'ratedenda' => 0.04,
                $header === 'kewajibanperbulan' => $kewajibanPerBulan,
                $header === 'keteranganjeniskredittopuptakeovernew' => $this->firstFilled($pelengkap?->jenis_kredit, 0),
                $header === 'kantorbayarsebelumnyakhusustakeover' => $this->firstFilled($pelengkap?->kantor_bayar, 0),
                $header === 'kodeao' => $this->firstFilled($pelengkap?->kode_ao, ''),
                $header === 'kodecabangdanlokasikredit' =>  $this->firstFilled($pelengkap?->cabang, ''),
                $header === 'giromitra' => '1007475094',
                $header === 'mitrachanneling' => 'KOPERASI NATA BUANA PASUNDAN',
                $header === 'rekeningdebitur' =>  $this->firstFilled($pelengkap?->norek, ''),
                $header === 'noagm' => '',
                $header === 'ketrcrs' => '',
                $header === 'tanggaldrop' =>  $this->firstFilled($pelengkap?->tanggal_dropping, ''),
                default => null,
            };

            if ($value !== null) {
                $cellValues[$column . $targetRow] = $value;
            }
        }

        return $cellValues;
    }

    private function buildRepaymentScheduleCellValues(DataSimulasi $dataSimulasi): array
    {
        $pelengkap = $dataSimulasi->pelengkap;

        $tenor = (int) ($dataSimulasi->tenor ?? 0);

        $jangkaWaktuTahun = ($tenor > 0 && $tenor % 12 === 0) ? (int) ($tenor / 12) : '';
        $jangkaWaktuBulan = ($tenor > 0 && $tenor % 12 !== 0) ? $tenor : '';

        $sukuBungaRaw = $this->asNumeric($this->firstFilled($pelengkap?->suku_bunga, 0));
        $sukuBunga = is_numeric($sukuBungaRaw) ? (float) $sukuBungaRaw : '';
        if (is_numeric($sukuBunga) && $sukuBunga > 1) {
            $sukuBunga = $sukuBunga / 100;
        }
        $bungaPerBulan = is_numeric($sukuBunga) ? $sukuBunga / 12 : '';
      
        $angsuran = $this->PMT($bungaPerBulan,$tenor,(float) ($this->asNumeric($dataSimulasi->plafond)) ?? 0);
        //     pokok: (float) ($this->asNumeric($dataSimulasi->plafond) ?? 0),
        //     bungaTahunan: $sukuBunga,
        //     tenorBulan: $tenor
        // );
   
        //$angsuran = (float) ($this->asNumeric($dataSimulasi->angsuran) ?? 0);
        // $adminAngsuran = (float) ($this->asNumeric(
        //     $this->firstFilled($pelengkap?->biaya_administrasi_angsuran_perbulan_baa, $dataSimulasi->biaya_adm_angs)
        // ) ?? 0);
        $adminAngsuran= (float) ($this->asNumeric($dataSimulasi->plafond)) * $bungaPerBulan;
       // $totalAngsuran = $angsuran + $adminAngsuran;

        return [
            'D4' => $this->asNumeric($dataSimulasi->plafond) ?? '',
            'D5' => $jangkaWaktuTahun,
            'D6' => $jangkaWaktuBulan,
            'D7' => $sukuBunga,
            'D8' => $bungaPerBulan,
            'D9' => $angsuran,
            'D10' => $this->toExcelDateSerial($pelengkap?->tanggal_dropping),
            'D11' => $this->toExcelDateSerial($pelengkap?->due_date_pertama),
        ];
    }
function PMT($rate, $nper, $pv, $fv = 0, $type = 0)
{

    
        Log::alert('Calculating PMT', [
        'rate' => $rate,
        'nper' => $nper,
        'pv' => $pv,
        'fv' => $fv,
        'type' => $type
    ]);
    if ($rate == 0) {
        return -($pv + $fv) / $nper;
    }

    $pow = pow(1 + $rate, $nper);

    $pmt = ($rate * ($fv + $pow * $pv)) / (($pow - 1) * (1 + $rate * $type));
    Log::alert('Calculated PMT', [
        'pmt' => $pmt
    ]);
    return $pmt;
}
function hitungAngsuranAnuitas($pokok, $bungaTahunan, $tenorBulan)
{
    // bunga per bulan (misal 12% / tahun = 1% per bulan)
    // $i = ($bungaTahunan*100 / 100) / 12;

    // // kalau bunga 0 (edge case)
    // if ($i == 0) {
    //     return $pokok / $tenorBulan;
    // }

    // rumus anuitas
    Log::alert('Calculating angsuran with hitungAngsuranAnuitas', [
        'pokok' => $pokok,
        'bungaTahunan' => $bungaTahunan,
        'tenorBulan' => $tenorBulan
    ]);
    $i=$bungaTahunan*100;
   $angsuran = ($pokok*$bungaTahunan/12)/(1-pow(1+$bungaTahunan/12,-$tenorBulan)); // + 10000
   //c32 : plafon
   //c21 : rate
   //C29 : tenor

    //$angsuran = $pokok * ($i * pow(1 + $i, $tenorBulan)) / (pow(1 + $i, $tenorBulan) - 1);

    return $angsuran;
}
    private function buildPermohonanCifCellValues(DataSimulasi $dataSimulasi): array
    {
        return $this->buildDefaultExcelCellValues($dataSimulasi);
    }

    private function generatePermohonanCifByHeaderTemplate(DataSimulasi $dataSimulasi): BinaryFileResponse
    {
        $templateFileName = 'permohonan_cif.xlsx';
        $templatePath = storage_path('upload/' . $templateFileName);

        if (!file_exists($templatePath)) {
            abort(Response::HTTP_NOT_FOUND, 'Template tidak ditemukan: ' . $templateFileName);
        }

        $downloadName = 'permohonan_cif_' . now()->format('Ymd_His') . '.xlsx';
        $tempDir = storage_path('app/temp');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . $downloadName;

        if (!copy($templatePath, $tempPath)) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal menyalin template Permohonan CIF.');
        }

        $headerData = $this->extractHeaderCellMap($tempPath);
        $targetRow = ($headerData['header_row'] ?? 1) + 1;
        $cellValues = $this->buildPermohonanCifHeaderMappedCellValues(
            $headerData['header_map'] ?? [],
            $targetRow,
            $dataSimulasi
        );

        $this->applyCellValuesToSheetXml($tempPath, $cellValues, array_keys($cellValues));

        return response()->download(
            $tempPath,
            $downloadName,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }

    private function buildPermohonanCifHeaderMappedCellValues(array $headerMap, int $targetRow, DataSimulasi $dataSimulasi): array
    {
        $pelengkap = $dataSimulasi->pelengkap;

        $namaDebitur = $this->firstFilled($pelengkap?->nama, $dataSimulasi->nama_debitur, '-');
        $alamatLengkap = trim((string) $this->firstFilled($pelengkap?->alamat, '') . ' ' . (string) $this->firstFilled($pelengkap?->alamat_2, ''));
        if ($alamatLengkap === '') {
            $alamatLengkap = '-';
        }

        $tempatTanggalLahir = $this->formatDate($dataSimulasi->tanggal_lahir);
        if ($tempatTanggalLahir) {
            $tempatTanggalLahir = '- / ' . $tempatTanggalLahir;
        }

        $statusPernikahan = $this->firstFilled($pelengkap?->nama_pasangan) ? 'Menikah' : '';
        $cellValues = [];

        foreach ($headerMap as $column => $header) {
            $value = match (true) {
                $header === 'no' => 1,
                str_contains($header, 'namapadadokumenidentitas') => $namaDebitur,
                str_contains($header, 'jeniskartuindentitas') => 'KTP',
                str_contains($header, 'nomordokumenindentitas') => $this->firstFilled($pelengkap?->no_ktp, '-'),
                str_contains($header, 'nomornpwp') => $this->firstFilled($pelengkap?->npwp, '-'),
                str_contains($header, 'jeniskelamin') => $this->firstFilled($pelengkap?->jenis_kelamin, '-'),
                str_contains($header, 'tempattanggallahir') => $this->firstFilled($tempatTanggalLahir, '-'),
                str_contains($header, 'namaibukandung') => $this->firstFilled($pelengkap?->nama_ibu_kandung, '-'),
                str_contains($header, 'jenisnasabah') => 'Perorangan',
                str_contains($header, 'statustempattinggal') => $this->firstFilled($pelengkap?->status_rumah, '-'),
                str_contains($header, 'alamatsesuai') => $alamatLengkap,
                $header === 'kodepos' => $this->firstFilled($pelengkap?->kode_pos, '-'),
                str_contains($header, 'alamatdomisili') => $alamatLengkap,
                str_contains($header, 'nomorteleponperusahaan') => '-',
                str_contains($header, 'nomorteleponemergencycall') => $this->firstFilled($pelengkap?->no_hp, '-'),
                str_contains($header, 'nomortelepon') => $this->firstFilled($pelengkap?->no_hp, '-'),
                str_contains($header, 'email') => '-',
                str_contains($header, 'rataratapenghasilan') => $this->asNumeric($dataSimulasi->gaji_pensiun),
                str_contains($header, 'kewarganegaraan') => 'WNI',
                str_contains($header, 'statuspernikahan') => $this->firstFilled($pelengkap?->status_kawin, '-'),
                str_contains($header, 'agama') => $this->firstFilled($pelengkap?->agama, '-'),
                str_contains($header, 'pendidikantertinggi') => $this->firstFilled($pelengkap?->pendidikan, '-'),
                str_contains($header, 'datapekerjaan') => 'Pensiunan',
                str_contains($header, 'namaperusahaan') => $this->firstFilled($dataSimulasi->instansi, '-'),
                str_contains($header, 'alamatperusahaan') => $alamatLengkap,
                str_contains($header, 'kotaperusahaan') => $this->firstFilled($pelengkap?->kota_kab, $pelengkap?->kota, '-'),
                str_contains($header, 'sumberdana') => 'Gaji Pensiunan',
                str_contains($header, 'maksimalnilaitransaksi') => $this->asNumeric($dataSimulasi->plafond),
                str_contains($header, 'namaemergencycall') => $this->firstFilled($pelengkap?->nama_pasangan, '-'),
                str_contains($header, 'hubunganemergencycall') => $this->firstFilled($pelengkap?->nama_pasangan) ? 'Pasangan' : '-',
                str_contains($header, 'alamatemergencycall') => $alamatLengkap,
                str_contains($header, 'mengenalbankkbbukopindari') => 'Koperasi Nata Buana Pasundan',
                default => null,
            };

            if ($value !== null) {
                $cellValues[$column . $targetRow] = $value;
            }
        }

        return $cellValues;
    }

    private function normalizeHeader(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/\s+/u', '', $text) ?? $text;

        return preg_replace('/[^a-z0-9]/u', '', $text) ?? $text;
    }

    private function buildPelunasanToKbCellValues(DataSimulasi $dataSimulasi): array
    {
        $pelengkap = $dataSimulasi->pelengkap;

        return array_filter([
            ...$this->buildDefaultExcelCellValues($dataSimulasi),
            'I19' => $this->asNumeric($dataSimulasi->pelunasan),
            'I20' => $this->asNumeric($pelengkap?->biaya_flagging),
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function generatePelunasanToKbByHeaderTemplate(DataSimulasi $dataSimulasi): BinaryFileResponse
    {
        $templateFileName = 'Pelunasan_TO_KB.xlsx';
        $templatePath = storage_path('upload/' . $templateFileName);

        if (!file_exists($templatePath)) {
            abort(Response::HTTP_NOT_FOUND, 'Template tidak ditemukan: ' . $templateFileName);
        }

        $downloadName = 'pelunasan_to_kb_' . now()->format('Ymd_His') . '.xlsx';
        $tempDir = storage_path('app/temp');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . $downloadName;

        if (!copy($templatePath, $tempPath)) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal menyalin template Pelunasan TO KB.');
        }

        $headerData = $this->extractHeaderCellMap($tempPath);
        $targetRow = ($headerData['header_row'] ?? 1) + 1;
        $cellValues = $this->buildPelunasanToKbHeaderMappedCellValues(
            $headerData['header_map'] ?? [],
            $targetRow,
            $dataSimulasi
        );

        $this->applyCellValuesToSheetXml($tempPath, $cellValues, array_keys($cellValues));

        return response()->download(
            $tempPath,
            $downloadName,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }

    private function buildPelunasanToKbHeaderMappedCellValues(array $headerMap, int $targetRow, DataSimulasi $dataSimulasi): array
    {
        $pelengkap = $dataSimulasi->pelengkap;

        $os = $this->asNumeric($dataSimulasi->pelunasan) ?? 0;
        $flagging = $this->asNumeric($this->firstFilled($pelengkap?->biaya_flagging, $dataSimulasi->amount_blokir_angsuran)) ?? 0;
        $bungaBerjalan = 0;
        $jumlah = (float) $os + (float) $flagging + (float) $bungaBerjalan;
        $cellValues = [];

        foreach ($headerMap as $column => $header) {
            $value = match (true) {
                $header === 'no' => 1,
                $header === 'nama' => $this->firstFilled($pelengkap?->nama, $dataSimulasi->nama_debitur, '-'),
                $header === 'nopin' => $this->firstFilled($dataSimulasi->nomor_pensiun, '-'),
                $header === 'os' => $os,
                $header === 'flagging' => $flagging,
                $header === 'bungaberjalan' => $bungaBerjalan,
                $header === 'jumlah' => $jumlah,
                default => null,
            };

            if ($value !== null) {
                $cellValues[$column . $targetRow] = $value;
            }
        }

        // Keep the total section consistent for templates without formulas.
        $cellValues['G6'] = $jumlah;

        return $cellValues;
    }

    private function extractHeaderCellMap(string $xlsxPath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($xlsxPath) !== true) {
            return ['header_row' => 1, 'header_map' => []];
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            return ['header_row' => 1, 'header_map' => []];
        }

        $sharedStrings = $this->readSharedStringsFromZip($zip);
        $zip->close();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($sheetXml);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = $xpath->query('//main:sheetData/main:row');
        if (!$rows) {
            return ['header_row' => 1, 'header_map' => []];
        }

        $bestRow = 1;
        $bestMap = [];
        $bestCount = 0;

        foreach ($rows as $rowNode) {
            if (!$rowNode instanceof \DOMElement) {
                continue;
            }

            $rowIndex = (int) ($rowNode->getAttribute('r') ?: 0);
            if ($rowIndex <= 0 || $rowIndex > 20) {
                continue;
            }

            $map = [];
            $cells = $xpath->query('main:c', $rowNode);
            if (!$cells) {
                continue;
            }

            foreach ($cells as $cellNode) {
                if (!$cellNode instanceof \DOMElement) {
                    continue;
                }

                $cellRef = $cellNode->getAttribute('r');
                $column = $this->columnFromCellRef($cellRef);
                if ($column === '') {
                    continue;
                }

                $raw = trim($this->extractCellTextFromNode($xpath, $cellNode, $sharedStrings));
                if ($raw === '') {
                    continue;
                }

                $map[$column] = $this->normalizeHeader($raw);
            }

            $nonEmptyCount = count(array_filter($map, fn ($v) => $v !== ''));
            if ($nonEmptyCount > $bestCount) {
                $bestCount = $nonEmptyCount;
                $bestMap = $map;
                $bestRow = $rowIndex;
            }
        }

        return [
            'header_row' => $bestRow,
            'header_map' => $bestMap,
        ];
    }

    private function readSharedStringsFromZip(ZipArchive $zip): array
    {
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml === false) {
            return [];
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($sharedXml);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $strings = [];
        $siNodes = $xpath->query('//main:si');
        if (!$siNodes) {
            return $strings;
        }

        foreach ($siNodes as $siNode) {
            if (!$siNode instanceof \DOMElement) {
                continue;
            }

            $parts = [];
            $tNodes = $xpath->query('.//main:t', $siNode);
            if ($tNodes) {
                foreach ($tNodes as $tNode) {
                    $parts[] = $tNode->textContent;
                }
            }

            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function extractCellTextFromNode(\DOMXPath $xpath, \DOMElement $cellNode, array $sharedStrings): string
    {
        $type = $cellNode->getAttribute('t');

        if ($type === 'inlineStr') {
            $tNodes = $xpath->query('main:is/main:t', $cellNode);
            if ($tNodes && $tNodes->length > 0) {
                return trim($tNodes->item(0)?->textContent ?? '');
            }

            return '';
        }

        $vNode = $xpath->query('main:v', $cellNode)->item(0);
        $value = $vNode?->textContent ?? '';

        if ($type === 's') {
            $index = (int) $value;
            return trim((string) ($sharedStrings[$index] ?? ''));
        }

        return trim((string) $value);
    }

    private function columnFromCellRef(string $cellRef): string
    {
        if (preg_match('/^([A-Z]+)/', $cellRef, $matches)) {
            return $matches[1];
        }

        return '';
    }

    private function buildDefaultExcelCellValues(DataSimulasi $dataSimulasi): array
    {
        $pelengkap = $dataSimulasi->pelengkap;

        return array_filter([
            'D3' => now()->translatedFormat('d F Y'),
            'I6' => $this->firstFilled($pelengkap?->nama, $dataSimulasi->nama_debitur),
            'I7' => $pelengkap?->no_ktp,
            'I8' => $this->formatDate($dataSimulasi->tanggal_lahir),
            'I9' => $this->firstFilled($pelengkap?->no, $pelengkap?->no_sppk, $pelengkap?->no_pk),
            'I10' => $dataSimulasi->nomor_pensiun,
            'I11' => $pelengkap?->alamat,
            'I12' => $dataSimulasi->instansi,
            'I16' => $this->asNumeric($dataSimulasi->plafond),
            'I17' => $dataSimulasi->tenor,
            'I18' => $this->asNumeric($dataSimulasi->angsuran),
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function applyCellValuesToSheetXml(
        string $xlsxPath,
        array $cellValues,
        array $forceCellOverrides = [],
        bool $applyToAllWorksheets = false
    ): void
    {
        $zip = new ZipArchive();

        if ($zip->open($xlsxPath) !== true) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Gagal membuka file hasil DNKA.');
        }

        $worksheetPaths = [];
        if ($applyToAllWorksheets) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (is_string($name) && preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                    $worksheetPaths[] = $name;
                }
            }
        }

        if ($worksheetPaths === []) {
            $worksheetPaths = ['xl/worksheets/sheet1.xml'];
        }

        foreach ($worksheetPaths as $sheetXmlPath) {
            $sheetXml = $zip->getFromName($sheetXmlPath);
            if ($sheetXml === false) {
                continue;
            }

            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = false;
            $dom->loadXML($sheetXml);

            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            foreach ($cellValues as $cellRef => $value) {
                $cellNode = $xpath->query("//main:c[@r='{$cellRef}']")->item(0);

                if (!$cellNode instanceof \DOMElement) {
                    $cellNode = $this->createCellNode($dom, $xpath, $cellRef);
                }

                if (!$cellNode instanceof \DOMElement) {
                    continue;
                }

                $formulaNode = $xpath->query('main:f', $cellNode)->item(0);

                if ($formulaNode instanceof \DOMElement && !in_array($cellRef, $forceCellOverrides, true)) {
                    // Never modify formula cells.
                    continue;
                }

                while ($cellNode->firstChild) {
                    $cellNode->removeChild($cellNode->firstChild);
                }

                if (is_int($value) || is_float($value)) {
                    $cellNode->removeAttribute('t');
                    $vNode = $dom->createElement('v', (string) $value);
                    $cellNode->appendChild($vNode);
                    continue;
                }

                $cellNode->setAttribute('t', 'inlineStr');
                $isNode = $dom->createElement('is');
                $tNode = $dom->createElement('t');
                $tNode->setAttribute('xml:space', 'preserve');
                $tNode->appendChild($dom->createTextNode((string) $value));
                $isNode->appendChild($tNode);
                $cellNode->appendChild($isNode);
            }

            $this->clearFormulaCachesInWorksheet($xpath);

            $zip->addFromString($sheetXmlPath, $dom->saveXML() ?: $sheetXml);
        }

        $this->enforceWorkbookRecalculation($zip);

        if ($zip->locateName('xl/worksheets/sheet1.xml') === false && !$applyToAllWorksheets) {
            $zip->close();
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Struktur worksheet template tidak ditemukan.');
        }

        $zip->close();

        $this->finalizeWorkbookWithCalculatedValues($xlsxPath);
    }

    private function finalizeWorkbookWithCalculatedValues(string $xlsxPath): void
    {
        $ioFactoryClass = 'PhpOffice\\PhpSpreadsheet\\IOFactory';
        $worksheetClass = 'PhpOffice\\PhpSpreadsheet\\Worksheet\\Worksheet';
        $richTextClass = 'PhpOffice\\PhpSpreadsheet\\RichText\\RichText';
        $dataTypeClass = 'PhpOffice\\PhpSpreadsheet\\Cell\\DataType';

        if (!class_exists($ioFactoryClass)) {
            Log::warning('Skip formula finalization because PhpSpreadsheet is not installed.', [
                'path' => $xlsxPath,
            ]);
            return;
        }

        try {
            $spreadsheet = $ioFactoryClass::load($xlsxPath);

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                if (!$worksheet instanceof $worksheetClass) {
                    continue;
                }

                $this->replaceFormulaCellsWithValues($worksheet, $richTextClass, $dataTypeClass);
            }

            $writer = $ioFactoryClass::createWriter($spreadsheet, 'Xlsx');
            $writer->setPreCalculateFormulas(false);
            $writer->save($xlsxPath);
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        } catch (\Throwable $e) {
            $message = 'Gagal finalisasi nilai formula Excel: ' . $e->getMessage();
            Log::error($message, ['path' => $xlsxPath]);
            error_log($message);
            // Fail-safe: keep generated workbook even when formula finalization fails in hosting environment.
            return;
        }
    }

    private function replaceFormulaCellsWithValues(mixed $worksheet, string $richTextClass, string $dataTypeClass): void
    {
        $coordinates = $worksheet->getCoordinates(false);

        foreach ($coordinates as $coordinate) {
            $cell = $worksheet->getCell($coordinate);

            if (!$cell->isFormula()) {
                continue;
            }

            try {
                $calculatedValue = $cell->getCalculatedValue();
            } catch (\Throwable) {
                $calculatedValue = $cell->getOldCalculatedValue();
            }

            if ($calculatedValue instanceof $richTextClass) {
                $calculatedValue = $calculatedValue->getPlainText();
            }

            if ($calculatedValue === null) {
                $worksheet->setCellValueExplicit($coordinate, '', $dataTypeClass::TYPE_STRING);
                continue;
            }

            if (is_bool($calculatedValue)) {
                $worksheet->setCellValueExplicit($coordinate, $calculatedValue ? 1 : 0, $dataTypeClass::TYPE_NUMERIC);
                continue;
            }

            if (is_int($calculatedValue) || is_float($calculatedValue)) {
                $worksheet->setCellValueExplicit($coordinate, $calculatedValue, $dataTypeClass::TYPE_NUMERIC);
                continue;
            }

            $worksheet->setCellValueExplicit($coordinate, (string) $calculatedValue, $dataTypeClass::TYPE_STRING);
        }
    }

    private function enforceWorkbookRecalculation(ZipArchive $zip): void
    {
        $workbookXmlPath = 'xl/workbook.xml';
        $workbookXml = $zip->getFromName($workbookXmlPath);

        if ($workbookXml === false) {
            return;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($workbookXml);

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $workbookNode = $xpath->query('/main:workbook')->item(0);
        if (!$workbookNode instanceof \DOMElement) {
            return;
        }

        $calcPrNode = $xpath->query('/main:workbook/main:calcPr')->item(0);
        if (!$calcPrNode instanceof \DOMElement) {
            $calcPrNode = $dom->createElementNS(
                'http://schemas.openxmlformats.org/spreadsheetml/2006/main',
                'calcPr'
            );
            $workbookNode->appendChild($calcPrNode);
        }

        $calcPrNode->setAttribute('calcMode', 'auto');
        $calcPrNode->setAttribute('fullCalcOnLoad', '1');
        $calcPrNode->setAttribute('forceFullCalc', '1');
        $calcPrNode->setAttribute('calcOnSave', '1');
        $calcPrNode->setAttribute('calcCompleted', '0');

        // Set very old calcId so Excel forces full recalculation using its current engine.
        $calcPrNode->setAttribute('calcId', '0');

        $zip->addFromString($workbookXmlPath, $dom->saveXML() ?: $workbookXml);

        $this->removeCalcChainArtifacts($zip);
    }

    private function removeCalcChainArtifacts(ZipArchive $zip): void
    {
        if ($zip->locateName('xl/calcChain.xml') !== false) {
            $zip->deleteName('xl/calcChain.xml');
        }

        $relsPath = 'xl/_rels/workbook.xml.rels';
        $relsXml = $zip->getFromName($relsPath);

        if ($relsXml !== false) {
            $relsDom = new \DOMDocument('1.0', 'UTF-8');
            $relsDom->preserveWhiteSpace = false;
            $relsDom->formatOutput = false;
            $relsDom->loadXML($relsXml);

            $relsXpath = new \DOMXPath($relsDom);
            $relsXpath->registerNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');

            $relationshipNodes = $relsXpath->query('/rel:Relationships/rel:Relationship');
            if ($relationshipNodes) {
                foreach (iterator_to_array($relationshipNodes) as $relationshipNode) {
                    if (!$relationshipNode instanceof \DOMElement) {
                        continue;
                    }

                    $type = $relationshipNode->getAttribute('Type');
                    $target = $relationshipNode->getAttribute('Target');

                    if (str_contains($type, '/calcChain') || $target === 'calcChain.xml') {
                        $relationshipNode->parentNode?->removeChild($relationshipNode);
                    }
                }
            }

            $zip->addFromString($relsPath, $relsDom->saveXML() ?: $relsXml);
        }

        $contentTypesPath = '[Content_Types].xml';
        $contentTypesXml = $zip->getFromName($contentTypesPath);

        if ($contentTypesXml !== false) {
            $contentTypesDom = new \DOMDocument('1.0', 'UTF-8');
            $contentTypesDom->preserveWhiteSpace = false;
            $contentTypesDom->formatOutput = false;
            $contentTypesDom->loadXML($contentTypesXml);

            $contentTypesXpath = new \DOMXPath($contentTypesDom);
            $contentTypesXpath->registerNamespace('ct', 'http://schemas.openxmlformats.org/package/2006/content-types');

            $overrideNodes = $contentTypesXpath->query('/ct:Types/ct:Override[@PartName="/xl/calcChain.xml"]');
            if ($overrideNodes) {
                foreach (iterator_to_array($overrideNodes) as $overrideNode) {
                    if ($overrideNode instanceof \DOMElement) {
                        $overrideNode->parentNode?->removeChild($overrideNode);
                    }
                }
            }

            $zip->addFromString($contentTypesPath, $contentTypesDom->saveXML() ?: $contentTypesXml);
        }
    }

    private function clearFormulaCachesInWorksheet(\DOMXPath $xpath): void
    {
        $formulaCells = $xpath->query('//main:sheetData//main:c[main:f]');
        if (!$formulaCells) {
            return;
        }

        foreach (iterator_to_array($formulaCells) as $cellNode) {
            if (!$cellNode instanceof \DOMElement) {
                continue;
            }

            $formulaNode = $xpath->query('main:f', $cellNode)->item(0);
            if ($formulaNode instanceof \DOMElement) {
                // Hint Excel that this formula cell must be recalculated.
                $formulaNode->setAttribute('ca', '1');
            }

            $valueNode = $xpath->query('main:v', $cellNode)->item(0);
            if ($valueNode instanceof \DOMElement) {
                $cellNode->removeChild($valueNode);
            }
        }
    }

    private function formatDate(mixed $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatDateSlash(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Handle Excel serial date values (e.g., 41954).
        if (is_numeric($value)) {
            try {
                $excelBase = \Illuminate\Support\Carbon::create(1899, 12, 30, 0, 0, 0)->startOfDay();
                return $excelBase->copy()->addDays((int) $value)->format('d/m/Y');
            } catch (\Throwable) {
                // Fall through to standard parse.
            }
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatDateCompact(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('Ymd');
        } catch (\Throwable) {
            $raw = preg_replace('/[^0-9]/', '', (string) $value) ?? '';
            return strlen($raw) >= 8 ? substr($raw, 0, 8) : '';
        }
    }

    private function toExcelDateSerial(mixed $value): int|string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            $date = \Illuminate\Support\Carbon::parse($value)->startOfDay();
            $excelBase = \Illuminate\Support\Carbon::create(1899, 12, 30, 0, 0, 0)->startOfDay();
            return $excelBase->diffInDays($date, false);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function asNumeric(mixed $value): float|int|string|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        $clean = preg_replace('/[^0-9,.-]/', '', (string) $value);

        if ($clean === null || $clean === '') {
            return (string) $value;
        }

        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? ($clean + 0) : (string) $value;
    }

    private function firstFilled(mixed ...$values): mixed
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function createCellNode(\DOMDocument $dom, \DOMXPath $xpath, string $cellRef): ?\DOMElement
    {
        if (!preg_match('/^([A-Z]+)(\d+)$/', $cellRef, $matches)) {
            return null;
        }

        $column = $matches[1];
        $rowIndex = (int) $matches[2];

        $rowNode = $xpath->query("//main:row[@r='{$rowIndex}']")->item(0);
        if (!$rowNode instanceof \DOMElement) {
            $sheetDataNode = $xpath->query('//main:sheetData')->item(0);
            if (!$sheetDataNode instanceof \DOMElement) {
                return null;
            }

            $rowNode = $dom->createElement('row');
            $rowNode->setAttribute('r', (string) $rowIndex);
            $sheetDataNode->appendChild($rowNode);
        }

        $cellNode = $dom->createElement('c');
        $cellNode->setAttribute('r', $cellRef);

        $insertBefore = null;
        foreach ($rowNode->childNodes as $childNode) {
            if (!$childNode instanceof \DOMElement || $childNode->tagName !== 'c') {
                continue;
            }

            $existingRef = $childNode->getAttribute('r');
            if ($this->compareCellRef($existingRef, $cellRef) > 0) {
                $insertBefore = $childNode;
                break;
            }
        }

        if ($insertBefore instanceof \DOMNode) {
            $rowNode->insertBefore($cellNode, $insertBefore);
        } else {
            $rowNode->appendChild($cellNode);
        }

        return $cellNode;
    }

    private function compareCellRef(string $first, string $second): int
    {
        [$firstColumn, $firstRow] = $this->splitCellRef($first);
        [$secondColumn, $secondRow] = $this->splitCellRef($second);

        if ($firstRow !== $secondRow) {
            return $firstRow <=> $secondRow;
        }

        return $firstColumn <=> $secondColumn;
    }

    private function splitCellRef(string $cellRef): array
    {
        if (!preg_match('/^([A-Z]+)(\d+)$/', $cellRef, $matches)) {
            return [0, 0];
        }

        return [$this->columnIndexFromLetters($matches[1]), (int) $matches[2]];
    }

    private function columnIndexFromLetters(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;

        for ($i = 0, $length = strlen($letters); $i < $length; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index;
    }
}
