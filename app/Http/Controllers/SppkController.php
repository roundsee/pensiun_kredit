<?php

namespace App\Http\Controllers;

use App\Models\DataSimulasi;
use App\Services\DocumentDataBuilderService;
use Barryvdh\DomPDF\Facade\Pdf;

class SppkController extends Controller
{
    public function preview(DataSimulasi $dataSimulasi)
    {
        $dataSimulasi->loadMissing('pelengkap');
        $data = DocumentDataBuilderService::buildSppkData($dataSimulasi);
        $data['is_preview'] = true;

        return view('sppk.template', $data);
    }

    public function generate(DataSimulasi $dataSimulasi)
    {
        $dataSimulasi->loadMissing('pelengkap');
        $data = DocumentDataBuilderService::buildSppkData($dataSimulasi);
        $data['is_preview'] = false;

        $pdf = Pdf::loadView('sppk.template', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('dpi', 150)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'serif');

        $filename = 'sppk_' . $dataSimulasi->id . '_' . now()->format('Ymd_His_u') . '.pdf';

        return $pdf->download($filename);
    }
}
