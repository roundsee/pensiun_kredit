@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Upload IDPB</h1>
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
                Upload dokumen IDPB untuk simulasi ini. Sistem akan ekstrak data otomatis, simpan ke data pelengkap,
                dan menyimpan file PDF per simulasi.
            </p>

            @if(!empty($pelengkap?->idpb_file))
                <div class="alert alert-secondary py-2">
                    File IDPB terakhir: <span class="fw-semibold">{{ $pelengkap->idpb_file }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('data_simulasi.idpb.upload', $dataSimulasi) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="idpb_pdf" class="form-label">File PDF IDPB</label>
                    <input type="file" class="form-control @error('idpb_pdf') is-invalid @enderror" id="idpb_pdf" name="idpb_pdf" accept="application/pdf" required>
                    @error('idpb_pdf')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Upload & Proses IDPB</button>
            </form>
        </div>
    </div>
</div>
@endsection
