@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Upload Permohonan CIF</h1>
            <div class="text-muted">Simulasi #{{ $dataSimulasi->id }} - {{ $dataSimulasi->nama_debitur ?: '-' }}</div>
        </div>
        <a href="{{ route('data_simulasi.list') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-3">
                Upload file Excel Permohonan CIF untuk simulasi ini dan disimpan per simulasi.
            </p>

            @if(!empty($pelengkap?->permohonan_cif_file))
                <div class="alert alert-secondary py-2">
                    File terakhir: <span class="fw-semibold">{{ $pelengkap->permohonan_cif_file }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('data_simulasi.permohonan_cif.upload', $dataSimulasi) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="permohonan_cif_file" class="form-label">File Excel Permohonan CIF</label>
                    <input type="file" class="form-control @error('permohonan_cif_file') is-invalid @enderror" id="permohonan_cif_file" name="permohonan_cif_file" accept=".xls,.xlsx" required>
                    @error('permohonan_cif_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Upload Permohonan CIF</button>
            </form>
        </div>
    </div>
</div>
@endsection
