<?php

namespace App\Http\Controllers;

use App\Models\DataSimulasi;
use App\Services\DocumentDataBuilderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerjanjianKreditController extends Controller
{
    /**
     * Generate PK PDF with selected template version.
     * 
     * @param DataSimulasi $dataSimulasi
     * @param Request $request - Query parameter: version (standard|kb)
     */
    public function generate(DataSimulasi $dataSimulasi, Request $request)
    {
        $dataSimulasi->loadMissing('pelengkap');

        $missingFields = $this->findMissingPerjanjianKreditFields($dataSimulasi);
        if ($missingFields !== []) {
            return redirect()
                ->route('data_simulasi.list')
                ->with('error', 'Perjanjian Kredit tidak bisa digenerate. Lengkapi data berikut: ' . implode(', ', $missingFields));
        }

        // Get version from query parameter, or fall back to saved preference in DB
        $version = $request->query('version', null);
        
        // If no version specified in query, use the saved preference
        if (!$version) {
            $dataSimulasi->loadMissing('pelengkap');
            $version = $dataSimulasi->pelengkap?->perjanjian_kredit_template_version ?? 'standard';
        }
        
        $validVersions = ['standard', 'kb'];
        
        if (!in_array($version, $validVersions)) {
            $version = 'standard';
        }

        $dataSimulasi->loadMissing('pelengkap');
        $data = DocumentDataBuilderService::buildPerjanjianKreditData($dataSimulasi);

        $templateName = match($version) {
            'kb' => 'perjanjian_kredit.template_kb',
            default => 'perjanjian_kredit.template',
        };

        $pdf = Pdf::loadView($templateName, $data)
            ->setPaper('a4', 'portrait')
            ->setOption('dpi', 150)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'serif');

        $versionSuffix = $version !== 'standard' ? '_' . $version : '';
        $filename = 'perjanjian_kredit' . $versionSuffix . '_' . $dataSimulasi->id . '_' . now()->format('Ymd_His') . '.pdf';
        $path = 'perjanjian_kredit/' . $filename;

        Storage::put($path, $pdf->output());

        return redirect()->route('perjanjian_kredit.download', ['dataSimulasi' => $dataSimulasi->id, 'filename' => $filename]);
    }

    public function download(DataSimulasi $dataSimulasi, string $filename)
    {
        $path = 'perjanjian_kredit/' . $filename;

        if (!Storage::exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::download($path, $filename);
    }

    private function findMissingPerjanjianKreditFields(DataSimulasi $dataSimulasi): array
    {
        $p = $dataSimulasi->pelengkap;

        return $this->collectMissingFields([
            'Nama Debitur' => $this->firstFilled($p?->nama, $dataSimulasi->nama_debitur),
            'No KTP' => $p?->no_ktp,
            'Tanggal Lahir' => $dataSimulasi->tanggal_lahir,
            'Alamat' => $p?->alamat,
            'Instansi' => $dataSimulasi->instansi,
            'Plafond' => $this->firstFilled($p?->plafond, $dataSimulasi->plafond),
            'Tenor' => $this->firstFilled($p?->jangka_waktu, $p?->jw, $dataSimulasi->tenor),
            'No PK/SPPK' => $this->firstFilled($p?->no_pk, $p?->no_sppk),
            'Tanggal PK/SPPK' => $this->firstFilled($p?->tanggal_pk, $p?->tgl_sppk, $p?->tanggal),
            'No SKEP' => $p?->no_skep,
            'Nama Perwakilan KB' => $p?->nama_perwakilan_kb,
            'Jabatan Perwakilan KB' => $p?->jabatan,
        ]);
    }

    private function collectMissingFields(array $requiredFields): array
    {
        $missingFields = [];
        foreach ($requiredFields as $label => $value) {
            if (!$this->isFilled($value)) {
                $missingFields[] = $label;
            }
        }

        return $missingFields;
    }

    private function isFilled(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    private function firstFilled(mixed ...$values): mixed
    {
        foreach ($values as $value) {
            if ($this->isFilled($value)) {
                return $value;
            }
        }

        return null;
    }
}
