<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ProductTemplate;
use App\Models\TemplateField;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ProductTemplateController extends Controller
{
    public function index()
    {
        $templates = ProductTemplate::with('templateFields')->paginate(10);
        return view('products.templates', compact('templates'));
    }

    public function create()
    {
        $accounts = Account::orderBy('code')->get();
        return view('products.template_create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_name'            => 'required|string|max:255',
            'template_description'     => 'nullable|string',
            'fields'                   => 'nullable|array',
            'fields.*.field_label'     => 'required|string|max:255',
            'fields.*.field_name'      => 'nullable|string|max:255',
            'fields.*.field_type'      => 'nullable|string|max:50',
            'fields.*.section'         => 'required|in:informasi_debitur,data_pengajuan,data_financial',
            'fields.*.account_code'    => 'nullable|string|max:50',
            'fields.*.calculation_type'=> 'nullable|in:percentage,fixed',
            'fields.*.default_value'   => 'nullable|numeric',
        ]);

        $template = ProductTemplate::create([
            'template_name'        => $request->template_name,
            'template_description' => $request->template_description,
        ]);

        $this->syncFields($template, $request->input('fields', []));

        return redirect()->route('product_templates.index')->with('success', 'Template berhasil dibuat.');
    }

    public function edit($id)
    {
        $template = ProductTemplate::with('templateFields')->findOrFail($id);
        $accounts = Account::orderBy('code')->get();
        return view('products.template_edit', compact('template', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'template_name'            => 'required|string|max:255',
            'template_description'     => 'nullable|string',
            'fields'                   => 'nullable|array',
            'fields.*.field_label'     => 'required|string|max:255',
            'fields.*.field_name'      => 'nullable|string|max:255',
            'fields.*.field_type'      => 'nullable|string|max:50',
            'fields.*.section'         => 'required|in:informasi_debitur,data_pengajuan,data_financial',
            'fields.*.account_code'    => 'nullable|string|max:50',
            'fields.*.calculation_type'=> 'nullable|in:percentage,fixed',
            'fields.*.default_value'   => 'nullable|numeric',
        ]);

        $template = ProductTemplate::findOrFail($id);
        $template->update([
            'template_name'        => $request->template_name,
            'template_description' => $request->template_description,
        ]);

        $template->templateFields()->delete();
        $this->syncFields($template, $request->input('fields', []));

        return redirect()->route('product_templates.index')->with('success', 'Template berhasil diupdate.');
    }

    public function destroy($id)
    {
        $template = ProductTemplate::findOrFail($id);
        $template->templateFields()->delete();
        $template->templateAccounts()->delete();
        $template->delete();
        return redirect()->route('product_templates.index')->with('success', 'Template berhasil dihapus.');
    }

    public function loadItems(ProductTemplate $productTemplate): JsonResponse
    {
        $productTemplate->load('templateFields');

        $debtorFields    = [];
        $submissionFields = [];
        $financialItems  = [];

        foreach ($productTemplate->templateFields as $tf) {
            $base = [
                'field_name'  => $tf->field_name,
                'field_label' => $tf->field_label,
                'field_type'  => $tf->field_type ?? 'text',
                'is_required' => (bool) $tf->is_required,
            ];

            if ($tf->section === 'informasi_debitur') {
                $debtorFields[] = array_merge($base, ['group' => 'informasi_debitur']);
            } elseif ($tf->section === 'data_pengajuan') {
                $submissionFields[] = array_merge($base, ['group' => 'data_pengajuan']);
            } elseif ($tf->section === 'data_financial') {
                $account = $tf->account_code
                    ? Account::query()->where('code', $tf->account_code)->first()
                    : null;

                $accountType     = $account?->type;
                $transactionType = in_array($accountType, ['asset', 'expense'], true) ? 'debit' : 'credit';

                $financialItems[] = [
                    'item_name'                   => $tf->field_name,
                    'account_id'                  => $account?->id,
                    'account_code'                => $tf->account_code,
                    'account_name'                => $tf->field_label,
                    'calculation_type'            => $tf->calculation_type ?? 'fixed',
                    'default_value'               => (float) ($tf->default_value ?? 0),
                    'transaction_type'            => $transactionType ?: 'credit',
                    'is_deducted_at_disbursement' => true,
                    'is_included_in_simulation'   => true,
                ];
            }
        }

        return response()->json([
            'template'   => [
                'id'          => $productTemplate->id,
                'name'        => $productTemplate->template_name,
                'description' => $productTemplate->template_description,
            ],
            'fields'     => array_merge($debtorFields, $submissionFields),
            'financials' => $financialItems,
        ]);
    }

    // ─── Private ────────────────────────────────────────────────────────────────

    private function syncFields(ProductTemplate $template, array $fields): void
    {
        foreach ($fields as $order => $f) {
            if (empty(trim($f['field_label'] ?? ''))) {
                continue;
            }

            TemplateField::create([
                'product_template_id' => $template->id,
                'field_name'          => Str::snake(trim($f['field_name'] ?? $f['field_label'])),
                'field_label'         => trim($f['field_label']),
                'field_type'          => $f['field_type'] ?? 'text',
                'is_required'         => (bool) ($f['is_required'] ?? false),
                'section'             => $f['section'],
                'account_code'        => isset($f['account_code']) && $f['account_code'] !== '' ? $f['account_code'] : null,
                'calculation_type'    => isset($f['calculation_type']) && $f['calculation_type'] !== '' ? $f['calculation_type'] : null,
                'default_value'       => isset($f['default_value']) && $f['default_value'] !== '' ? $f['default_value'] : null,
                'field_order'         => (int) $order,
            ]);
        }
    }
}
