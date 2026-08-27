@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Tambah PAM</h1>

    <form method="POST" action="{{ route('pam.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Produk</label>
                <input type="text" name="produk" class="form-control" value="{{ old('produk') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kantor Bayar</label>
                <input type="text" name="kantor_bayar" class="form-control" value="{{ old('kantor_bayar') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Plafond Min</label>
                <input type="number" step="0.01" name="plafond_min" class="form-control" value="{{ old('plafond_min') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Plafond Max</label>
                <input type="number" step="0.01" name="plafond_max" class="form-control" value="{{ old('plafond_max') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tenor Max</label>
                <input type="number" name="tenor_max" class="form-control" value="{{ old('tenor_max') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Rate %</label>
                <input type="number" step="0.000001" name="rate_percent" class="form-control" value="{{ old('rate_percent') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Provisi %</label>
                <input type="number" step="0.000001" name="provisi_percent" class="form-control" value="{{ old('provisi_percent') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Usia Masuk Min</label>
                <input type="number" name="usia_masuk_min" class="form-control" value="{{ old('usia_masuk_min') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Usia Max</label>
                <input type="number" name="usia_max" class="form-control" value="{{ old('usia_max') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Admin %</label>
                <input type="number" step="0.000001" name="admin_percent" class="form-control" value="{{ old('admin_percent') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Blokir Angsuran</label>
                <input type="number" name="blokir_angsuran" class="form-control" value="{{ old('blokir_angsuran') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Taspen</label>
                <input type="number" step="0.01" name="taspen" class="form-control" value="{{ old('taspen') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tata Laksana</label>
                <input type="number" step="0.01" name="tata_laksana" class="form-control" value="{{ old('tata_laksana') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tata Laksana + %</label>
                <input type="number" step="0.000001" name="tata_laksana_plus_percent" class="form-control" value="{{ old('tata_laksana_plus_percent') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Admin Angsuran %</label>
                <input type="number" step="0.000001" name="admin_angsuran_percent" class="form-control" value="{{ old('admin_angsuran_percent') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">DBR %</label>
                <input type="number" step="0.000001" name="dbr_percent" class="form-control" value="{{ old('dbr_percent') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Asabri</label>
                <input type="number" step="0.01" name="asabri" class="form-control" value="{{ old('asabri') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Usia Masuk Max</label>
                <input type="number" name="usia_masuk_max" class="form-control" value="{{ old('usia_masuk_max') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('pam.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
