<?php

namespace App\Http\Controllers;

use App\Models\ProductStruct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductStructController extends Controller
{
    public function index()
    {
        $items = ProductStruct::query()
            ->orderBy('sort_order')
            ->orderBy('produk')
            ->paginate(20);

        return view('products.product_structs', compact('items'));
    }

    public function create()
    {
        return view('products.product_struct_create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk' => ['required', 'string', 'max:100', 'unique:product_struct,produk'],
            'kantor_bayar' => ['nullable', 'string', 'max:255'],
            'plafond_min' => ['nullable', 'numeric'],
            'plafond_max' => ['nullable', 'numeric'],
            'tenor_max' => ['nullable', 'integer'],
            'rate_percent' => ['nullable', 'numeric'],
            'provisi_percent' => ['nullable', 'numeric'],
            'usia_masuk_min' => ['nullable', 'integer'],
            'usia_max' => ['nullable', 'integer'],
            'admin_percent' => ['nullable', 'numeric'],
            'blokir_angsuran' => ['nullable', 'integer'],
            'taspen' => ['nullable', 'numeric'],
            'tata_laksana' => ['nullable', 'numeric'],
            'tata_laksana_plus_percent' => ['nullable', 'numeric'],
            'admin_angsuran_percent' => ['nullable', 'numeric'],
            'dbr_percent' => ['nullable', 'numeric'],
            'asabri' => ['nullable', 'numeric'],
            'usia_masuk_max' => ['nullable', 'integer'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        ProductStruct::create($validated);

        return redirect()->route('pam.index')->with('success', 'Data PAM berhasil ditambahkan.');
    }

    public function edit(ProductStruct $productStruct)
    {
        return view('products.product_struct_edit', compact('productStruct'));
    }

    public function update(Request $request, ProductStruct $productStruct)
    {
        $validated = $request->validate([
            'produk' => ['required', 'string', 'max:100', Rule::unique('product_struct', 'produk')->ignore($productStruct->id)],
            'kantor_bayar' => ['nullable', 'string', 'max:255'],
            'plafond_min' => ['nullable', 'numeric'],
            'plafond_max' => ['nullable', 'numeric'],
            'tenor_max' => ['nullable', 'integer'],
            'rate_percent' => ['nullable', 'numeric'],
            'provisi_percent' => ['nullable', 'numeric'],
            'usia_masuk_min' => ['nullable', 'integer'],
            'usia_max' => ['nullable', 'integer'],
            'admin_percent' => ['nullable', 'numeric'],
            'blokir_angsuran' => ['nullable', 'integer'],
            'taspen' => ['nullable', 'numeric'],
            'tata_laksana' => ['nullable', 'numeric'],
            'tata_laksana_plus_percent' => ['nullable', 'numeric'],
            'admin_angsuran_percent' => ['nullable', 'numeric'],
            'dbr_percent' => ['nullable', 'numeric'],
            'asabri' => ['nullable', 'numeric'],
            'usia_masuk_max' => ['nullable', 'integer'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $productStruct->update($validated);

        return redirect()->route('pam.index')->with('success', 'Data PAM berhasil diperbarui.');
    }

    public function destroy(ProductStruct $productStruct)
    {
        $productStruct->delete();

        return redirect()->route('pam.index')->with('success', 'Data PAM berhasil dihapus.');
    }
}
