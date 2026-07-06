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

        $missingFields = $this->findMissingSppkFields($dataSimulasi);
        if ($missingFields !== []) {
            return redirect()
                ->route('data_simulasi.list')
                ->with('error', 'SPPK tidak bisa digenerate. Lengkapi data berikut: ' . implode(', ', $missingFields));
        }

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

    private function findMissingSppkFields(DataSimulasi $dataSimulasi): array
    {
        $p = $dataSimulasi->pelengkap;

        return $this->collectMissingFields([
            'Nama Debitur' => $this->firstFilled($p?->nama, $dataSimulasi->nama_debitur),
            'No KTP' => $p?->no_ktp,
            'Alamat' => $p?->alamat,
            'No SPPK / No PK' => $this->firstFilled($p?->no_sppk, $p?->no_pk),
            'Tanggal SPPK / PK' => $this->firstFilled($p?->tgl_sppk, $p?->tanggal_pk, $p?->tanggal),
            'Plafond' => $this->firstFilled($p?->plafond, $dataSimulasi->plafond),
            'Jangka Waktu' => $this->firstFilled($p?->jangka_waktu, $p?->jw, $dataSimulasi->tenor),
            'Suku Bunga' => $p?->suku_bunga,
            'Asuransi Jiwa Kredit' => $this->firstFilled($p?->asuransi_jiwa_kredit, $dataSimulasi->asuransi),
            'No SKEP' => $p?->no_skep,
            'Nama Kuasa KB/Bank' => $this->firstFilled($p?->nama_petugas_nbp, $p?->nama_perwakilan_kb),
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
