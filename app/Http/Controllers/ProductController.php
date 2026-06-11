<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductField;
use App\Models\ProductFinancial;
use App\Models\Account;
use App\Models\ProductTemplate;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.create', [
            'templates' => ProductTemplate::query()->orderBy('template_name')->get(['id', 'template_name', 'template_description']),
            'accounts' => Account::query()->select(['id', 'code', 'name', 'type'])->orderBy('code')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $product = DB::transaction(function () use ($validated): Product {
            $product = Product::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'],
            ]);

            $this->syncProductFields($product, $validated['fields'] ?? []);
            $this->syncProductFinancials($product, $validated['financials'] ?? []);

            return $product;
        });

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $product->load(['fields', 'financials.account']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->load(['fields', 'financials.account']);
        return view('products.edit', compact('product'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($product, $validated): void {
            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'],
            ]);

            $this->syncProductFields($product, $validated['fields'] ?? []);
            $this->syncProductFinancials($product, $validated['financials'] ?? []);
        });

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function configuration(Product $product): JsonResponse
    {
        $product->load(['fields', 'financials.account']);

        return response()->json([
            'product' => $product,
            'fields' => $product->fields,
            'financials' => $product->financials,
        ]);
    }

    public function engineOptions(): JsonResponse
    {
        $fieldCatalog = [
            ['field_name' => 'tgl_lahir', 'field_label' => 'Tanggal Lahir', 'field_type' => 'date', 'group' => 'informasi_debitur'],
            ['field_name' => 'nama', 'field_label' => 'Nama Debitur', 'field_type' => 'text', 'group' => 'informasi_debitur'],
            ['field_name' => 'status_perkawinan', 'field_label' => 'Status Perkawinan', 'field_type' => 'dropdown', 'group' => 'informasi_debitur'],
            ['field_name' => 'instansi_pensiun', 'field_label' => 'Instansi Pensiun', 'field_type' => 'text', 'group' => 'informasi_debitur'],
            ['field_name' => 'nopen', 'field_label' => 'Nomor Pensiun', 'field_type' => 'text', 'group' => 'informasi_debitur'],
            ['field_name' => 'gaji_bersih', 'field_label' => 'Gaji Bersih', 'field_type' => 'number', 'group' => 'informasi_debitur'],
            ['field_name' => 'tenor', 'field_label' => 'Tenor', 'field_type' => 'number', 'group' => 'data_pengajuan'],
            ['field_name' => 'blokir', 'field_label' => 'Blokir', 'field_type' => 'number', 'group' => 'data_pengajuan'],
            ['field_name' => 'bank', 'field_label' => 'Bank', 'field_type' => 'dropdown', 'group' => 'data_pengajuan'],
        ];

        $financialCatalog = [
            'administrasi',
            'provisi',
            'asuransi',
            'angsuran',
            'plafon',
            'admin_angsuran',
            'pelunasan',
            'tatalaksana',
        ];

        return response()->json([
            'field_catalog' => $fieldCatalog,
            'financial_catalog' => $financialCatalog,
            'accounts' => Account::query()->select(['id', 'code', 'name', 'type'])->orderBy('code')->get(),
        ]);
    }

    private function syncProductFields(Product $product, array $fields): void
    {
        $keepIds = [];

        foreach ($fields as $field) {
            $row = ProductField::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'field_name' => $field['field_name'],
                    'group' => $field['group'],
                ],
                [
                    'field_label' => $field['field_label'],
                    'field_type' => $field['field_type'],
                    'is_required' => (bool) ($field['is_required'] ?? true),
                ]
            );

            $keepIds[] = $row->id;
        }

        ProductField::query()
            ->where('product_id', $product->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    private function syncProductFinancials(Product $product, array $financials): void
    {
        $keepIds = [];

        foreach ($financials as $item) {
            $row = ProductFinancial::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'item_name' => $item['item_name'],
                ],
                [
                    'account_id' => $item['account_id'],
                    'calculation_type' => $item['calculation_type'],
                    'default_value' => $item['default_value'],
                    'transaction_type' => $item['transaction_type'],
                    'is_deducted_at_disbursement' => (bool) ($item['is_deducted_at_disbursement'] ?? true),
                    'is_included_in_simulation' => (bool) ($item['is_included_in_simulation'] ?? true),
                ]
            );

            $keepIds[] = $row->id;
        }

        ProductFinancial::query()
            ->where('product_id', $product->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}
