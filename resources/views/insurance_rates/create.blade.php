@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Tambah Insurance Rate</h1>

    @if($copiedInsuranceRate)
        <div class="alert alert-info mb-3">
            Mengisi form berdasarkan data produk <strong>{{ $copiedInsuranceRate->product }}</strong>, bank <strong>{{ $copiedInsuranceRate->bank_tujuan ?? '-' }}</strong>, dan tipe <strong>{{ filled($copiedInsuranceRate->usia) ? 'Usia' : 'Tenor' }}</strong>.
        </div>
    @endif

    <form method="POST" action="{{ route('insurance_rates.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Produk</label>
                <input type="text" name="product" class="form-control" value="{{ old('product', $copiedInsuranceRate?->product ?? '') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Bank Tujuan</label>
                <input type="text" name="bank_tujuan" class="form-control" value="{{ old('bank_tujuan', $copiedInsuranceRate?->bank_tujuan ?? '') }}" placeholder="contoh: BRI, KB, MANTAP">
            </div>

            <div class="col-12">
                <label class="form-label d-block">Jenis Rate</label>
                <div class="btn-group" role="group" aria-label="Jenis rate">
                    <input type="radio" class="btn-check" name="rate_type" id="rate_type_tenor" value="tenor" {{ old('rate_type', $rateType ?? 'tenor') === 'tenor' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="rate_type_tenor">Berdasarkan Tenor</label>

                    <input type="radio" class="btn-check" name="rate_type" id="rate_type_usia" value="usia" {{ old('rate_type', $rateType ?? 'tenor') === 'usia' ? 'checked' : '' }}>
                    <label class="btn btn-outline-primary" for="rate_type_usia">Berdasarkan Usia</label>
                </div>
            </div>

            <div class="col-md-4 tenor-field" {{ old('rate_type', $rateType ?? 'tenor') === 'usia' ? 'style=display:none;' : '' }}>
                <label class="form-label">Tenor</label>
                <input type="number" name="tenor" class="form-control" value="{{ old('tenor', $copiedInsuranceRate?->tenor ?? '') }}" min="1">
            </div>
            <div class="col-md-4 usia-field" {{ old('rate_type', $rateType ?? 'tenor') === 'tenor' ? 'style=display:none;' : '' }}>
                <label class="form-label">Usia</label>
                <input type="text" name="usia" class="form-control" value="{{ old('usia', $copiedInsuranceRate?->usia ?? '') }}" placeholder="contoh: 55-60 atau 60">
            </div>
            <div class="col-md-4">
                <label class="form-label">Premium / 1jt</label>
                <input type="number" step="0.0001" min="0" name="premium_per_million" class="form-control" value="{{ old('premium_per_million', $copiedInsuranceRate?->premium_per_million ?? '') }}" required>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('insurance_rates.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tenorField = document.querySelector('.tenor-field');
        const usiaField = document.querySelector('.usia-field');
        const radios = document.querySelectorAll('input[name="rate_type"]');

        function toggleRateType() {
            const selected = document.querySelector('input[name="rate_type"]:checked')?.value ?? 'tenor';
            if (tenorField) {
                tenorField.style.display = selected === 'tenor' ? '' : 'none';
                const tenorInput = tenorField.querySelector('input');
                if (tenorInput) {
                    tenorInput.disabled = selected !== 'tenor';
                }
            }
            if (usiaField) {
                usiaField.style.display = selected === 'usia' ? '' : 'none';
                const usiaInput = usiaField.querySelector('input');
                if (usiaInput) {
                    usiaInput.disabled = selected !== 'usia';
                }
            }
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', toggleRateType);
        });

        toggleRateType();
    });
</script>
@endsection
