<?php

namespace App\Http\Controllers;

use App\Models\DataSimulasi;
use App\Models\MailMergeTemplate;
use App\Services\PdfTextExtractionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DataSimulasiController extends Controller
{
    public function index()
    {
        $dataSimulasi = DataSimulasi::query()
            ->with('pelengkap')
            ->latest('id')
            ->paginate(15);

        $mailMergeTemplates = MailMergeTemplate::query()
            ->orderBy('document_type')
            ->orderBy('name')
            ->get(['id', 'name', 'document_type']);

        return view('products.data_simulasi_index', compact('dataSimulasi', 'mailMergeTemplates'));
    }

    public function edit(DataSimulasi $dataSimulasi)
    {
        $fields = $this->fields();
        $dateFields = $this->dataSimulasiDateFields();
        $numericFields = $this->dataSimulasiNumericFields();

        return view('products.data_simulasi_edit', compact('dataSimulasi', 'fields', 'dateFields', 'numericFields'));
    }

    public function update(Request $request, DataSimulasi $dataSimulasi)
    {
        $validated = $request->validate($this->rules());

        $dataSimulasi->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Data simulasi berhasil diupdate.',
                'id' => $dataSimulasi->id,
                'data' => $dataSimulasi->fresh(),
            ]);
        }

        return redirect()
            ->route('data_simulasi.list')
            ->with('success', 'Data simulasi berhasil diupdate.');
    }

    public function destroy(DataSimulasi $dataSimulasi)
    {
        $dataSimulasi->delete();

        return redirect()
            ->route('data_simulasi.list')
            ->with('success', 'Data simulasi berhasil dihapus.');
    }

    public function editPelengkap(DataSimulasi $dataSimulasi)
    {
        $fields = $this->pelengkapFields();
        $pelengkap = $dataSimulasi->pelengkap;
        $usageMap = $this->pelengkapUsageMap();
        $dateFields = $this->pelengkapDateFields();
        $numericFields = $this->pelengkapNumericFields();

        return view('products.data_simulasi_pelengkap_edit', compact('dataSimulasi', 'fields', 'pelengkap', 'usageMap', 'dateFields', 'numericFields'));
    }

    public function updatePelengkap(Request $request, DataSimulasi $dataSimulasi)
    {
        $validated = $request->validate($this->pelengkapRules());

        // PERBAIKAN: Cari berdasarkan data_simulasi_id!
        $dataSimulasi->pelengkap()->updateOrCreate(
            ['data_simulasi_id' => $dataSimulasi->id],
            $validated
         );

        return redirect()
        ->route('data_simulasi.list')
        ->with('success', 'Data pelengkap simulasi berhasil disimpan.');

    }

    public function showUploadIdpb(DataSimulasi $dataSimulasi)
    {
        $pelengkap = $dataSimulasi->pelengkap;

        return view('products.data_simulasi_idpb_upload', compact('dataSimulasi', 'pelengkap'));
    }

    public function uploadIdpb(Request $request, DataSimulasi $dataSimulasi, PdfTextExtractionService $pdfTextExtractionService)
    {
        $request->validate([
            'idpb_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $file = $request->file('idpb_pdf');
        $folder = 'upload/idpb/' . $dataSimulasi->id;
        $filename = now()->format('Ymd_His') . '_' . preg_replace('/\s+/', '_', (string) $file->getClientOriginalName());
        $storedPath = $file->storeAs($folder, $filename);

        try {
            $lines = $pdfTextExtractionService->extractLines(Storage::path($storedPath));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca PDF IDPB: ' . $e->getMessage());
        }

        $rows = $this->parsePdfKeyValueRows($lines);
        $mapped = $this->mapPdfRowsToPelengkap($rows, $lines);
        $mapped = array_filter($mapped, static fn ($value) => trim((string) $value) !== '');

        if ($mapped === []) {
            return back()->with('error', 'PDF IDPB berhasil diupload, tetapi belum ada field pelengkap yang cocok untuk disimpan.');
        }

        $mapped['idpb_file'] = $storedPath;

        $dataSimulasi->pelengkap()->updateOrCreate([], $mapped);

        return redirect()
            ->route('data_simulasi.list')
            ->with('success', 'Upload IDPB berhasil untuk simulasi #' . $dataSimulasi->id . ' (' . count($mapped) . ' field tersimpan).');
    }

    public function showUploadPermohonanCif(DataSimulasi $dataSimulasi)
    {
        $pelengkap = $dataSimulasi->pelengkap;

        return view('products.data_simulasi_permohonan_cif_upload', compact('dataSimulasi', 'pelengkap'));
    }

    public function uploadPermohonanCif(Request $request, DataSimulasi $dataSimulasi)
    {
        $request->validate([
            'permohonan_cif_file' => ['required', 'file', 'mimes:xls,xlsx', 'max:10240'],
        ]);

        $file = $request->file('permohonan_cif_file');
        $folder = 'upload/permohonan_cif/' . $dataSimulasi->id;
        $filename = now()->format('Ymd_His') . '_' . preg_replace('/\s+/', '_', (string) $file->getClientOriginalName());
        $storedPath = $file->storeAs($folder, $filename);

        $dataSimulasi->pelengkap()->updateOrCreate([], ['permohonan_cif_file' => $storedPath]);

        return redirect()
            ->route('data_simulasi.list')
            ->with('success', 'Upload Permohonan CIF berhasil untuk simulasi #' . $dataSimulasi->id . '.');
    }

    public function showUploadPelunasanToKb(DataSimulasi $dataSimulasi)
    {
        $pelengkap = $dataSimulasi->pelengkap;

        return view('products.data_simulasi_pelunasan_to_kb_upload', compact('dataSimulasi', 'pelengkap'));
    }

    public function uploadPelunasanToKb(Request $request, DataSimulasi $dataSimulasi)
    {
        $request->validate([
            'pelunasan_to_kb_file' => ['required', 'file', 'mimes:xls,xlsx', 'max:10240'],
        ]);

        $file = $request->file('pelunasan_to_kb_file');
        $folder = 'upload/pelunasan_to_kb/' . $dataSimulasi->id;
        $filename = now()->format('Ymd_His') . '_' . preg_replace('/\s+/', '_', (string) $file->getClientOriginalName());
        $storedPath = $file->storeAs($folder, $filename);

        $dataSimulasi->pelengkap()->updateOrCreate([], ['pelunasan_to_kb_file' => $storedPath]);

        return redirect()
            ->route('data_simulasi.list')
            ->with('success', 'Upload Pelunasan TO KB berhasil untuk simulasi #' . $dataSimulasi->id . '.');
    }

    public function extractPelengkapFromPdf(Request $request, DataSimulasi $dataSimulasi, PdfTextExtractionService $pdfTextExtractionService)
    {
        $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $storedPath = $request->file('pdf_file')->store('upload');

        try {
            $lines = $pdfTextExtractionService->extractLines(Storage::path($storedPath));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal membaca PDF: ' . $e->getMessage(),
                'stored_path' => $storedPath,
            ], 422);
        }

        $rows = $this->parsePdfKeyValueRows($lines);
        $mapped = $this->mapPdfRowsToPelengkap($rows, $lines);
        $mapped = array_filter($mapped, static fn ($value) => trim((string) $value) !== '');

        if ($mapped === []) {
            return response()->json([
                'message' => 'PDF berhasil dibaca, tetapi belum ada field pelengkap yang cocok untuk disimpan.',
                'stored_path' => $storedPath,
                'data' => [],
            ], 422);
        }

        $dataSimulasi->pelengkap()->updateOrCreate([], $mapped);

        return response()->json([
            'message' => 'OCR PDF pelengkap berhasil diproses dan disimpan (' . count($mapped) . ' field).',
            'stored_path' => $storedPath,
            'data' => $mapped,
        ]);
    }

    private function fields(): array
    {
        return [
            'jenis_pensiun',
            'nama_debitur',
            'tanggal_lahir',
            'umur',
            'nomor_pensiun',
            'instansi',
            'gaji_pensiun',
            'sisa_gaji_saat_pengajuan',
            'produk',
            'mutasi',
            'bank_asal',
            'bank_tujuan',
            'tenor_max',
            'plafond_max',
            'tenor',
            'nama_marketing',
            'kode_area',
            'usia_lunas',
            'tgl_permohonan',
            'tgl_lunas',
            'blokir_angsuran',
            'plafond',
            'angsuran',
            'biaya_adm_angs',
            'total_angsuran',
            'provisi',
            'administrasi',
            'asuransi',
            'extra_premi',
            'amount_blokir_angsuran',
            'pelunasan',
            'tata_laksana',
            'total_biaya',
            'sisa_gaji_akhir',
            'terima_bersih',
        ];
    }

    private function rules(): array
    {
        $rules = [];
        $dateFields = array_flip($this->dataSimulasiDateFields());
        $numericFields = array_flip($this->dataSimulasiNumericFields());

        foreach ($this->fields() as $field) {
            if (isset($dateFields[$field])) {
                $rules[$field] = ['nullable', 'date'];
                continue;
            }

            if (isset($numericFields[$field])) {
                $rules[$field] = ['nullable', 'numeric'];
                continue;
            }

            $rules[$field] = ['nullable', 'string'];
        }

        return $rules;
    }

    private function dataSimulasiDateFields(): array
    {
        return [
            'tanggal_lahir',
            'tgl_permohonan',
            'tgl_lunas',
        ];
    }

    private function dataSimulasiNumericFields(): array
    {
        return [
            'umur',
            'gaji_pensiun',
            'sisa_gaji_saat_pengajuan',
            'tenor_max',
            'plafond_max',
            'tenor',
            'usia_lunas',
            'blokir_angsuran',
            'plafond',
            'angsuran',
            'biaya_adm_angs',
            'total_angsuran',
            'provisi',
            'administrasi',
            'asuransi',
            'extra_premi',
            'amount_blokir_angsuran',
            'pelunasan',
            'total_biaya',
            'sisa_gaji_akhir',
            'terima_bersih',
        ];
    }

    private function pelengkapFields(): array
    {
        $allFields = [
            'tgl_sppk' => 'TANGGAL SPPK',
            'no_sppk' => 'NO SPPK',
            'alamat' => 'ALAMAT',
            'alamat_2' => 'ALAMAT 2',
            'kota' => 'KOTA',
            'tgl_surat_pernyataan_kuasa_potong_gaji' => 'TANGGAL SURAT PERNYATAAN KUASA POTONG GAJI',
            'no_skep' => 'NO SKEP',
            'no_pk' => 'NO PK',
            'tanggal_pk' => 'TANGGAL PK',
            'no_ktp' => 'NO KTP',
            'nama_perwakilan_kb' => 'NAMA PERWAKILAN KB',
            'jabatan' => 'JABATAN',
            'no_surat_kuasa_substitusi' => 'NO SURAT KUASA SUBSTITUSI',
            'tanggal_surat_kuasa_substitusi' => 'TANGGAL SURAT KUASA SUBSTITUSI',
            'no_hp' => 'NO HP',
            'norek' => 'NOREK',
            'cabang_kb' => 'CABANG KB',
            'nama_ao' => 'NAMA AO',
            'kode_ao' => 'KODE AO',
            'ket' => 'KET',
            'kantor_bayar' => 'KANTOR BAYAR',
            'no_si' => 'NO SI',

            'npwp' => 'NPWP',
            'nama_ibu_kandung' => 'NAMA IBU KANDUNG',
            'tanggal_dropping' => 'TANGGAL DROPPING',
            'due_date_pertama' => 'DUE DATE PERTAMA',
            'suku_bunga' => 'SUKU BUNGA',
            'jenis_fasilitas' => 'JENIS FASILITAS',
            'bentuk_fasilitas' => 'BENTUK FASILITAS',
            'prosentase_provisi' => 'PROSENTASE PROVISI',
            'prosentase_administrasi' => 'PROSENTASE ADMINISTRASI',
            'asuransi' => 'ASURANSI',
            'materai' => 'MATERAI',
            'tanggal_skep' => 'TANGGAL SKEP',
            'prosentase_admin_bank' => 'PROSENTASE ADMIN BANK',
            'angsuran_dibayar_dimuka' => 'ANGSURAN DIBAYAR DIMUKA',
            'nama_pasangan' => 'NAMA PASANGAN',
            'ktp_pasangan' => 'KTP PASANGAN',
            'tgl_lahir_pasangan' => 'TGL LAHIR PASANGAN',
            'cabang' => 'CABANG',
            'jenis_kredit' => 'JENIS KREDIT',
            'status_kawin' => 'STATUS KAWIN',
            'agama' => 'AGAMA',
            'jenis_kelamin' => 'JENIS KELAMIN',
            'pendidikan' => 'PENDIDIKAN',
            'rt' => 'RT',
            'rw' => 'RW',
            'kode_pos' => 'KODE POS',
            'kec' => 'KECAMATAN',
            'kel' => 'KELURAHAN',

        ];

        return $this->filterByExistingPelengkapColumns($allFields);
    }

    private function pelengkapRules(): array
    {
        $rules = [];
        $dateFields = array_flip($this->pelengkapDateFields());
        $numericFields = array_flip($this->pelengkapNumericFields());

        foreach (array_keys($this->pelengkapFields()) as $field) {
            if (isset($dateFields[$field])) {
                $rules[$field] = ['nullable', 'date'];
                continue;
            }

            if (isset($numericFields[$field])) {
                $rules[$field] = ['nullable', 'numeric'];
                continue;
            }

            $rules[$field] = ['nullable', 'string'];
        }

        return $rules;
    }

    private function pelengkapDateFields(): array
    {
        return [
            'tgl_sppk',
            'tanggal_dropping',
            'due_date_pertama',
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
    }

    private function pelengkapNumericFields(): array
    {
        return [
            'suku_bunga',
            'prosentase_provisi',
            'prosentase_administrasi',
            'asuransi',
            'materai',
            'prosentase_admin_bank',
            'angsuran_dibayar_dimuka',
            'plafond',
            'biaya_provisi',
            'biaya_administrasi_kredit',
            'asuransi_jiwa_kredit',
            'biaya_flagging',
            'total_biaya',
            'total_penerimaan',
            'angsuran_pokok_bunga_perbulan',
            'biaya_administrasi_angsuran_perbulan_baa',
            'angsuran_bank_baa',
            'nominal_penarikan',
            'jangka_waktu',
            'jw',
            'kali_angsuran',
        ];
    }

    private function pelengkapUsageMap(): array
    {
        return [
            'tgl_sppk' => ['pk' => false, 'sppk' => true],
            'no_sppk' => ['pk' => false, 'sppk' => true],
            'npwp' => ['pk' => false, 'sppk' => false],
            'nama_ibu_kandung' => ['pk' => false, 'sppk' => false],
            'tanggal_dropping' => ['pk' => false, 'sppk' => false],
            'due_date_pertama' => ['pk' => false, 'sppk' => false],
            'kota' => ['pk' => true, 'sppk' => true],
            'alamat' => ['pk' => true, 'sppk' => true],
            'alamat_2' => ['pk' => true, 'sppk' => true],
            'suku_bunga' => ['pk' => true, 'sppk' => true],
            'jenis_fasilitas' => ['pk' => true, 'sppk' => true],
            'bentuk_fasilitas' => ['pk' => true, 'sppk' => true],
            'prosentase_provisi' => ['pk' => true, 'sppk' => true],
            'prosentase_administrasi' => ['pk' => true, 'sppk' => true],
            'asuransi' => ['pk' => true, 'sppk' => true],
            'materai' => ['pk' => true, 'sppk' => true],
            'tgl_surat_pernyataan_kuasa_potong_gaji' => ['pk' => false, 'sppk' => true],
            'no_pk' => ['pk' => true, 'sppk' => true],
            'tanggal_pk' => ['pk' => true, 'sppk' => true],
            'nama_perwakilan_kb' => ['pk' => true, 'sppk' => true],
            'jabatan' => ['pk' => true, 'sppk' => true],
            'no_surat_kuasa_substitusi' => ['pk' => false, 'sppk' => true],
            'tanggal_surat_kuasa_substitusi' => ['pk' => false, 'sppk' => true],
            'no_ktp' => ['pk' => true, 'sppk' => true],
            'no_skep' => ['pk' => true, 'sppk' => true],
            'tanggal_skep' => ['pk' => false, 'sppk' => false],
            'no_hp' => ['pk' => false, 'sppk' => false],
            'prosentase_admin_bank' => ['pk' => true, 'sppk' => true],
            'angsuran_dibayar_dimuka' => ['pk' => true, 'sppk' => true],
            'norek' => ['pk' => true, 'sppk' => false],
            'cabang_kb' => ['pk' => true, 'sppk' => true],
            'nama_ao' => ['pk' => false, 'sppk' => false],
            'kode_ao' => ['pk' => false, 'sppk' => false],
            'ket' => ['pk' => false, 'sppk' => false],
            'nama_pasangan' => ['pk' => false, 'sppk' => false],
            'ktp_pasangan' => ['pk' => false, 'sppk' => false],
            'tgl_lahir_pasangan' => ['pk' => false, 'sppk' => false],
            'cabang' => ['pk' => false, 'sppk' => false],
            'no_si' => ['pk' => false, 'sppk' => false],
        ];
    }
    
    private function parsePdfKeyValueRows(array $lines): array
    {
        $rows = [];

        foreach ($lines as $line) {
            if (preg_match('/^(.{2,}?)\s*[:\-]\s*(.+)$/u', $line, $m)) {
                $rows[] = [
                    'label' => trim((string) $m[1]),
                    'value' => trim((string) $m[2]),
                ];
                continue;
            }

            if (preg_match('/^(.{2,}?)\s{2,}(.+)$/u', $line, $m)) {
                $rows[] = [
                    'label' => trim((string) $m[1]),
                    'value' => trim((string) $m[2]),
                ];
            }
        }

        return $rows;
    }

    private function mapPdfRowsToPelengkap(array $rows, array $lines): array
    {
        $rowMap = [];
        foreach ($rows as $row) {
            $label = $this->normalizeLabel((string) ($row['label'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($label === '' || $value === '') {
                continue;
            }

            if (!array_key_exists($label, $rowMap)) {
                $rowMap[$label] = $value;
            }
        }

        $payload = [];
        foreach ($this->pelengkapFields() as $field => $headerLabel) {
            $normalizedHeader = $this->normalizeLabel($headerLabel);
            if (isset($rowMap[$normalizedHeader])) {
                $payload[$field] = $rowMap[$normalizedHeader];
            }
        }

        $aliases = [
            'no_skep' => ['no skep', 'nomor skep', 'no sk', 'nomor sk'],
            'tanggal_skep' => ['tanggal skep', 'tgl skep', 'tanggal sk', 'tgl sk'],
            'alamat' => ['alamat', 'alamat lengkap'],
            'nama' => ['nama debitur', 'nama nasabah', 'nama pensiunan'],
            'no_ktp' => ['nik', 'no ktp', 'nomor ktp'],
            'npwp' => ['npwp', 'no npwp', 'nomor npwp'],
            'nama_ibu_kandung' => ['nama ibu kandung', 'nama ibu', 'nama gadis ibu kandung'],
            'kota' => ['kota', 'kabupaten', 'kota kabupaten'],
        ];

        foreach ($aliases as $field => $candidates) {
            if (!$this->hasPelengkapColumn($field)) {
                continue;
            }

            if (isset($payload[$field]) && trim((string) $payload[$field]) !== '') {
                continue;
            }

            foreach ($candidates as $candidate) {
                $key = $this->normalizeLabel($candidate);
                if (isset($rowMap[$key])) {
                    $payload[$field] = $rowMap[$key];
                    break;
                }
            }
        }

        if (!isset($payload['no_skep']) || trim((string) $payload['no_skep']) === '') {
            $fallback = $this->extractNoSkepFromLines($lines);
            if ($fallback !== null) {
                $payload['no_skep'] = $fallback;
            }
        }

        if ($this->hasPelengkapColumn('tanggal_skep')
            && (!isset($payload['tanggal_skep']) || trim((string) $payload['tanggal_skep']) === '')) {
            $fallback = $this->extractTanggalSkepFromLines($lines);
            if ($fallback !== null) {
                $payload['tanggal_skep'] = $fallback;
            }
        }

        if (!isset($payload['alamat']) || trim((string) $payload['alamat']) === '') {
            $fallback = $this->extractAlamatFromLines($lines);
            if ($fallback !== null) {
                $payload['alamat'] = $fallback;
            }
        }

        return $this->filterPayloadByExistingPelengkapColumns($payload);
    }

    private function hasPelengkapColumn(string $column): bool
    {
        return Schema::hasColumn('data_simulasi_pelengkap', $column);
    }

    private function filterByExistingPelengkapColumns(array $fields): array
    {
        $filtered = [];

        foreach ($fields as $column => $label) {
            if ($this->hasPelengkapColumn($column)) {
                $filtered[$column] = $label;
            }
        }

        return $filtered;
    }

    private function filterPayloadByExistingPelengkapColumns(array $payload): array
    {
        $filtered = [];

        foreach ($payload as $column => $value) {
            if ($this->hasPelengkapColumn((string) $column)) {
                $filtered[$column] = $value;
            }
        }

        return $filtered;
    }

    private function normalizeLabel(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        $text = str_replace(['.', ':', ';', ',', '-', '_', '/'], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return mb_strtolower(trim($text));
    }

    private function extractNoSkepFromLines(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:NO(?:MOR)?\.?\s*SKEP|SKEP)\s*[:\-]?\s*(.+)$/iu', $line, $m)) {
                $value = trim((string) $m[1]);
                $value = preg_split('/\b(?:TGL|TANGGAL)\b/iu', $value)[0] ?? $value;
                $value = trim((string) $value);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    private function extractTanggalSkepFromLines(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:TGL|TANGGAL)\s*(?:SKEP|SKEP)?\s*[:\-]?\s*(\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{2,4})/iu', $line, $m)) {
                return trim((string) $m[1]);
            }

            if (stripos($line, 'skep') !== false && preg_match('/(\d{1,2}[\/.\-]\d{1,2}[\/.\-]\d{2,4})/u', $line, $m)) {
                return trim((string) $m[1]);
            }
        }

        return null;
    }

    private function extractAlamatFromLines(array $lines): ?string
    {
        $stopPattern = '/^(?:NO\.?\s*KTP|NO\.?\s*SKEP|SKEP|NAMA|TEMPAT|TGL|TANGGAL|PEKERJAAN|JABATAN)\b/iu';

        foreach ($lines as $index => $line) {
            if (!preg_match('/^ALAMAT\b\s*[:\-]?\s*(.*)$/iu', $line, $m)) {
                continue;
            }

            $parts = [];
            $first = trim((string) ($m[1] ?? ''));
            if ($first !== '') {
                $parts[] = $first;
            }

            for ($i = 1; $i <= 2; $i++) {
                $next = $lines[$index + $i] ?? null;
                if ($next === null) {
                    break;
                }

                $next = trim((string) $next);
                if ($next === '' || preg_match($stopPattern, $next)) {
                    break;
                }

                $parts[] = $next;
            }

            $alamat = trim(implode(' ', $parts));
            if ($alamat !== '') {
                return $alamat;
            }
        }

        return null;
    }
}