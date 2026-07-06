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

        $missingFields = $this->findMissingSiFields($dataSimulasi);
        if ($missingFields !== []) {
            return redirect()
                ->route('data_simulasi.list')
                ->with('error', 'SI TO tidak bisa digenerate. Lengkapi data berikut: ' . implode(', ', $missingFields));
        }

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

        $missingFields = $this->findMissingSiFields($dataSimulasi);
        if ($missingFields !== []) {
            return redirect()
                ->route('data_simulasi.list')
                ->with('error', 'SI New/Topup tidak bisa digenerate. Lengkapi data berikut: ' . implode(', ', $missingFields));
        }

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

    private function findMissingSiFields(DataSimulasi $dataSimulasi): array
    {
        $p = $dataSimulasi->pelengkap;

        return $this->collectMissingFields([
            'Nama Debitur' => $this->firstFilled($p?->nama, $dataSimulasi->nama_debitur),
            'No KTP' => $p?->no_ktp,
            'Tanggal Lahir' => $dataSimulasi->tanggal_lahir,
            'Nomor Pensiun' => $dataSimulasi->nomor_pensiun,
            'Instansi' => $dataSimulasi->instansi,
            'Alamat' => $p?->alamat,
            'Plafond' => $this->firstFilled($p?->plafond, $dataSimulasi->plafond),
            'Tenor' => $this->firstFilled($p?->jangka_waktu, $p?->jw, $dataSimulasi->tenor),
            'No SKEP' => $p?->no_skep,
            'No PK/SPPK' => $this->firstFilled($p?->no_pk, $p?->no_sppk, $p?->no),
            'Tanggal PK/SPPK' => $this->firstFilled($p?->tanggal_pk, $p?->tgl_sppk, $p?->tanggal),
            'Kota' => $this->firstFilled($p?->kota_kab, $p?->kota),
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