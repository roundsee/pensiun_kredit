<?php

namespace App\Http\Controllers;

use App\Models\DataSimulasi;
use App\Models\DataSimulasiPelengkap;
use App\Models\MailMergeTemplate;
use App\Services\DocumentDataBuilderService;
use App\Services\PdfTextExtractionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MailMergeController extends Controller
{
    public function __construct(private readonly PdfTextExtractionService $pdfTextExtractionService)
    {
    }

    public function index(): View
    {
        $templates = MailMergeTemplate::query()->latest('id')->paginate(15);

        return view('mail_merge.index', compact('templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'in:perjanjian_kredit,sppk,si,other'],
            'template_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $pdfPath = $request->file('template_pdf')->store('mail_merge/templates');
        $absolutePath = Storage::path($pdfPath);

        $lines = $this->pdfTextExtractionService->extractLines($absolutePath);
        [$templateHtml, $slots] = $this->buildTemplateHtml($lines);

        $template = MailMergeTemplate::query()->create([
            'name' => $validated['name'],
            'document_type' => $validated['document_type'],
            'source_pdf_path' => $pdfPath,
            'template_html' => $templateHtml,
            'slot_definitions' => $slots,
            'mappings' => [],
        ]);

        $viewName = $this->generateBladeTemplate($template);
        $template->update(['generated_view_path' => $viewName]);

        return redirect()
            ->route('mail_merge.edit', $template)
            ->with('success', 'Template berhasil digenerate. Silakan lakukan mapping field.');
    }

    public function storeExisting(Request $request): RedirectResponse
    {
        $knownViews = [
            'sppk' => 'sppk.template',
            'perjanjian_kredit' => 'perjanjian_kredit.template',
        ];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'in:perjanjian_kredit,sppk'],
        ]);

        $bladeView = $knownViews[$validated['document_type']]
            ?? abort(422, 'Tidak ada existing blade untuk tipe dokumen ini.');

        $template = MailMergeTemplate::query()->create([
            'name' => $validated['name'],
            'document_type' => $validated['document_type'],
            'existing_blade_view' => $bladeView,
            'template_html' => '',
            'slot_definitions' => [],
            'mappings' => [],
        ]);

        return redirect()
            ->route('mail_merge.index')
            ->with('success', 'Template "' . $template->name . '" berhasil didaftarkan menggunakan blade existing.');
    }

    public function edit(MailMergeTemplate $mailMergeTemplate): View
    {
        $tableOptions = [
            ['key' => 'data_simulasi', 'label' => 'Data Simulasi'],
            ['key' => 'data_simulasi_pelengkap', 'label' => 'Data Simulasi Pelengkap'],
        ];

        return view('mail_merge.edit', [
            'template' => $mailMergeTemplate,
            'tableOptions' => $tableOptions,
            'mappingsJson' => json_encode($mailMergeTemplate->mappings ?? [], JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function fields(Request $request): JsonResponse
    {
        $table = (string) $request->query('table', '');

        $fields = match ($table) {
            'data_simulasi' => (new DataSimulasi())->getFillable(),
            'data_simulasi_pelengkap' => array_values(array_filter(
                (new DataSimulasiPelengkap())->getFillable(),
                static fn ($field) => $field !== 'data_simulasi_id'
            )),
            default => [],
        };

        return response()->json([
            'table' => $table,
            'fields' => $fields,
        ]);
    }

    public function update(Request $request, MailMergeTemplate $mailMergeTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'mappings' => ['nullable', 'json'],
            'template_html' => ['nullable', 'string'],
        ]);

        $decoded = [];
        if (!empty($validated['mappings'])) {
            $decoded = json_decode($validated['mappings'], true);
            if (!is_array($decoded)) {
                $decoded = [];
            }
        }

        $templateHtml = $validated['template_html'] ?? ($mailMergeTemplate->template_html ?? '');

        $mailMergeTemplate->update([
            'mappings' => $decoded,
            'template_html' => $templateHtml,
            'slot_definitions' => $this->extractSlotsFromHtml($templateHtml),
        ]);

        $viewName = $this->generateBladeTemplate($mailMergeTemplate->fresh());
        $mailMergeTemplate->update(['generated_view_path' => $viewName]);

        return redirect()
            ->route('mail_merge.edit', $mailMergeTemplate)
            ->with('success', 'Mapping mail merge berhasil disimpan.');
    }

    public function preview(MailMergeTemplate $mailMergeTemplate, DataSimulasi $dataSimulasi): View
    {
        $mailMergeTemplate = $mailMergeTemplate->fresh();
        $dataSimulasi->loadMissing('pelengkap');

        if ($mailMergeTemplate->existing_blade_view) {
            $data = $this->buildExistingTemplateData($mailMergeTemplate, $dataSimulasi);
            $data['is_preview'] = true;

            return view($mailMergeTemplate->existing_blade_view, $data);
        }

        abort_unless($mailMergeTemplate->generated_view_path, 404, 'Generated view template tidak ditemukan.');

        return view($mailMergeTemplate->generated_view_path, [
            'mailMergeData' => $this->buildMailMergeData($mailMergeTemplate, $dataSimulasi),
        ]);
    }

    public function download(MailMergeTemplate $mailMergeTemplate, DataSimulasi $dataSimulasi): Response
    {
        $mailMergeTemplate = $mailMergeTemplate->fresh();
        $dataSimulasi->loadMissing('pelengkap');

        $filename = str($mailMergeTemplate->name)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
        $filename = ($filename !== '' ? $filename : 'mail_merge') . '_' . $dataSimulasi->id . '.pdf';

        if ($mailMergeTemplate->existing_blade_view) {
            $data = $this->buildExistingTemplateData($mailMergeTemplate, $dataSimulasi);
            $data['is_preview'] = false;
            $viewName = $mailMergeTemplate->existing_blade_view;
        } else {
            abort_unless($mailMergeTemplate->generated_view_path, 404, 'Generated view template tidak ditemukan.');
            $viewName = $mailMergeTemplate->generated_view_path;
            $data = ['mailMergeData' => $this->buildMailMergeData($mailMergeTemplate, $dataSimulasi)];
        }

        $pdf = Pdf::loadView($viewName, $data)
            ->setPaper('a4', 'portrait')
            ->setOption('dpi', 150)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'serif');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildTemplateHtml(array $lines): array
    {
        $slotIndex = 1;
        $slots = [];
        $htmlLines = [];

        foreach ($lines as $line) {
            $escaped = e($line);

            $lineHtml = preg_replace_callback('/\.{4,}/', function ($matches) use (&$slotIndex, &$slots) {
                $slotId = 'slot_' . $slotIndex;
                $slotIndex++;

                $slots[] = [
                    'id' => $slotId,
                    'original' => $matches[0],
                ];

                return '<span class="merge-slot" data-slot="' . $slotId . '">[ pilih field ]</span>';
            }, $escaped) ?? $escaped;

            $htmlLines[] = '<p class="tpl-line">' . $lineHtml . '</p>';
        }

        return [implode("\n", $htmlLines), $slots];
    }

    private function generateBladeTemplate(MailMergeTemplate $template): string
    {
        $viewName = 'mail_merge.generated.template_' . $template->id;
        $bladePath = resource_path('views/mail_merge/generated/template_' . $template->id . '.blade.php');

        $directory = dirname($bladePath);
        if (!is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $html = $template->template_html ?? '';
        $bladeBody = preg_replace_callback(
            '/<span\s+class="merge-slot"\s+data-slot="([^"]+)">.*?<\/span>/s',
            static fn ($m) => '{{ $mailMergeData[\'' . $m[1] . '\'] ?? \'' . str_repeat('.', 12) . '\' }}',
            $html
        ) ?? $html;

        $content = "<!DOCTYPE html>\n" .
            "<html lang=\"id\">\n" .
            "<head>\n" .
            "    <meta charset=\"UTF-8\">\n" .
            "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>\n" .
            "    <title>Mail Merge Template - " . e($template->name) . "</title>\n" .
            "    <style>body{font-family:'Times New Roman',serif;font-size:11pt;line-height:1.4;color:#000;} .tpl-line{margin:0 0 6px 0;}</style>\n" .
            "</head>\n" .
            "<body>\n" .
            $bladeBody . "\n" .
            "</body>\n" .
            "</html>\n";

        File::put($bladePath, $content);

        return $viewName;
    }

    private function extractSlotsFromHtml(string $templateHtml): array
    {
        if (trim($templateHtml) === '') {
            return [];
        }

        preg_match_all('/data-slot="([^"]+)"/', $templateHtml, $matches);
        $slotIds = $matches[1] ?? [];

        return array_values(array_map(
            static fn ($id) => ['id' => $id, 'original' => 'selected_text'],
            array_unique($slotIds)
        ));
    }

    private function buildExistingTemplateData(MailMergeTemplate $template, DataSimulasi $dataSimulasi): array
    {
        return match ($template->document_type) {
            'sppk' => DocumentDataBuilderService::buildSppkData($dataSimulasi),
            'perjanjian_kredit' => DocumentDataBuilderService::buildPerjanjianKreditData($dataSimulasi),
            default => [],
        };
    }

    private function buildMailMergeData(MailMergeTemplate $template, DataSimulasi $dataSimulasi): array
    {
        $dataSimulasi->loadMissing('pelengkap');
        $pelengkap = $dataSimulasi->pelengkap;
        $mappings = $template->mappings ?? [];
        $payload = [];

        foreach ($mappings as $slotId => $mapping) {
            $table = $mapping['table'] ?? null;
            $field = $mapping['field'] ?? null;

            if (!$table || !$field) {
                continue;
            }

            $payload[$slotId] = match ($table) {
                'data_simulasi' => $this->stringifyMailMergeValue($dataSimulasi->{$field} ?? null),
                'data_simulasi_pelengkap' => $this->stringifyMailMergeValue($pelengkap?->{$field} ?? null),
                default => '',
            };
        }

        return $payload;
    }

    private function stringifyMailMergeValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        return trim((string) $value);
    }
}