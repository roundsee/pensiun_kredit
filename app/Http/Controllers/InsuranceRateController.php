<?php

namespace App\Http\Controllers;

use App\Models\InsuranceRate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsuranceRateController extends Controller
{
    public function index()
    {
        $items = InsuranceRate::query()
            ->orderBy('product')
            ->orderBy('bank_tujuan')
            ->orderBy('tenor')
            ->orderBy('usia')
            ->paginate(20);

        return view('insurance_rates.index', compact('items'));
    }

    public function create(Request $request)
    {
        $copiedInsuranceRate = null;
        $copyId = $request->query('copy');

        if ($copyId !== null && $copyId !== '') {
            $copiedInsuranceRate = InsuranceRate::query()->find($copyId);
        }

        $rateType = old('rate_type', $copiedInsuranceRate?->usia !== null && $copiedInsuranceRate?->usia !== '' ? 'usia' : 'tenor');

        return view('insurance_rates.create', compact('copiedInsuranceRate', 'rateType'));
    }

    public function store(Request $request)
    {
        $rateType = $request->input('rate_type', 'tenor');

        $validated = $request->validate([
            'rate_type' => ['required', 'in:tenor,usia'],
            'product' => ['required', 'string', 'max:100'],
            'bank_tujuan' => ['nullable', 'string', 'max:255'],
            'tenor' => ['nullable', 'integer', 'min:1'],
            'usia' => ['nullable', 'string', 'max:50'],
            'premium_per_million' => ['required', 'numeric', 'min:0'],
        ]);

        if ($rateType === 'tenor') {
            $validated['usia'] = null;
            $request->validate(['tenor' => ['required', 'integer', 'min:1']]);
        } else {
            $validated['tenor'] = null;
            $request->validate(['usia' => ['required', 'string', 'max:50']]);
        }

        InsuranceRate::create($validated);

        return redirect()->route('insurance_rates.index')->with('success', 'Data Insurance Rate berhasil ditambahkan.');
    }

    public function edit(InsuranceRate $insuranceRate)
    {
        $rateType = filled($insuranceRate->usia) ? 'usia' : 'tenor';

        return view('insurance_rates.edit', compact('insuranceRate', 'rateType'));
    }

    public function update(Request $request, InsuranceRate $insuranceRate)
    {
        $rateType = $request->input('rate_type', filled($insuranceRate->usia) ? 'usia' : 'tenor');

        $validated = $request->validate([
            'rate_type' => ['required', 'in:tenor,usia'],
            'product' => ['required', 'string', 'max:100'],
            'bank_tujuan' => ['nullable', 'string', 'max:255'],
            'tenor' => ['nullable', 'integer', 'min:1'],
            'usia' => ['nullable', 'string', 'max:50'],
            'premium_per_million' => ['required', 'numeric', 'min:0'],
        ]);

        if ($rateType === 'tenor') {
            $validated['usia'] = null;
            $request->validate(['tenor' => ['required', 'integer', 'min:1']]);
        } else {
            $validated['tenor'] = null;
            $request->validate(['usia' => ['required', 'string', 'max:50']]);
        }

        $insuranceRate->update($validated);

        return redirect()->route('insurance_rates.index')->with('success', 'Data Insurance Rate berhasil diperbarui.');
    }

    public function destroy(InsuranceRate $insuranceRate)
    {
        $insuranceRate->delete();

        return redirect()->route('insurance_rates.index')->with('success', 'Data Insurance Rate berhasil dihapus.');
    }
}
