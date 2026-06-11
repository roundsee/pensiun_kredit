<?php

namespace App\Http\Controllers;

use App\Models\DataSimulasi;
use App\Services\DocumentDataBuilderService;
use Barryvdh\DomPDF\Facade\Pdf;
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
}
