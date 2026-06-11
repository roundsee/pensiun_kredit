@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Upload Pelunasan TO KB</h1>
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
                Upload file Excel Pelunasan TO KB untuk simulasi ini dan disimpan per simulasi.
            </p>

            @if(!empty($pelengkap?->pelunasan_to_kb_file))
                <div class="alert alert-secondary py-2">
                    File terakhir: <span class="fw-semibold">{{ $pelengkap->pelunasan_to_kb_file }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('data_simulasi.pelunasan_to_kb.upload', $dataSimulasi) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="pelunasan_to_kb_file" class="form-label">File Excel Pelunasan TO KB</label>
                    <input type="file" class="form-control @error('pelunasan_to_kb_file') is-invalid @enderror" id="pelunasan_to_kb_file" name="pelunasan_to_kb_file" accept=".xls,.xlsx" required>
                    @error('pelunasan_to_kb_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Upload Pelunasan TO KB</button>
            </form>
        </div>
    </div>
</div>
@endsection
