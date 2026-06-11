<?php

namespace App\Http\Controllers;

use App\Models\DataSimulasi;
use App\Services\DocumentDataBuilderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SiController extends Controller
{
    public function generateTakeOver(DataSimulasi $dataSimulasi): RedirectResponse
    {
        $dataSimulasi->loadMissing('pelengkap');
        $data = DocumentDataBuilderService::buildSiTakeOverData($dataSimulasi);

        $pdf = Pdf::loadView('si.template_to', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('dpi', 150)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'serif');

        $filename = 'si_kb_to_' . $dataSimulasi->id . '_' . now()->format('Ymd_His') . '.pdf';
        Storage::put('si/' . $filename, $pdf->output());

        return redirect()->route('si.download', [
            'dataSimulasi' => $dataSimulasi->id,
            'filename' => $filename,
        ]);
    }

    public function generateNewTopup(DataSimulasi $dataSimulasi): RedirectResponse
    {
        $dataSimulasi->loadMissing('pelengkap');
        $data = DocumentDataBuilderService::buildSiNewTopupData($dataSimulasi);

        $pdf = Pdf::loadView('si.template_new_topup', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('dpi', 150)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'serif');

        $filename = 'si_kb_new_topup_' . $dataSimulasi->id . '_' . now()->format('Ymd_His') . '.pdf';
        Storage::put('si/' . $filename, $pdf->output());

        return redirect()->route('si.download', [
            'dataSimulasi' => $dataSimulasi->id,
            'filename' => $filename,
        ]);
    }

    public function download(DataSimulasi $dataSimulasi, string $filename): BinaryFileResponse
    {
        $path = 'si/' . $filename;

        if (!Storage::exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download(Storage::path($path), $filename);
    }
}