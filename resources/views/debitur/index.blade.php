@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Debitur Info</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('debitur.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="filter-nopen" class="form-label mb-1">Nopen</label>
                    <input
                        type="text"
                        id="filter-nopen"
                        name="nopen"
                        class="form-control"
                        value="{{ $filters['nopen'] }}"
                        placeholder="Cari nomor pensiun"
                    >
                </div>
                <div class="col-md-3">
                    <label for="filter-nama" class="form-label mb-1">Nama</label>
                    <input
                        type="text"
                        id="filter-nama"
                        name="nama"
                        class="form-control"
                        value="{{ $filters['nama'] }}"
                        placeholder="Cari nama debitur"
                    >
                </div>
                <div class="col-md-2">
                    <label for="filter-nik" class="form-label mb-1">NIK</label>
                    <input
                        type="text"
                        id="filter-nik"
                        name="nik"
                        class="form-control"
                        value="{{ $filters['nik'] }}"
                        placeholder="Cari NIK"
                    >
                </div>
                <div class="col-md-2">
                    <label for="filter-hp" class="form-label mb-1">No. HP</label>
                    <input
                        type="text"
                        id="filter-hp"
                        name="hp"
                        class="form-control"
                        value="{{ $filters['hp'] }}"
                        placeholder="Cari no. HP"
                    >
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('debitur.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($debitur->isEmpty())
                <div class="p-3 text-muted">
                    Belum ada data debitur.
                    Data akan otomatis dibuat saat sebuah simulasi di-confirm.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nopen</th>
                                <th>Nama</th>
                                <th>NIK</th>
                                <th>Tgl Lahir</th>
                                <th>Agama</th>
                                <th>Pekerjaan</th>
                                <th>Instansi</th>
                                <th>No. HP</th>
                                <th style="min-width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($debitur as $row)
                                <tr>
                                    <td>{{ $loop->iteration + (($debitur->currentPage() - 1) * $debitur->perPage()) }}</td>
                                    <td>{{ $row->nopen ?: '-' }}</td>
                                    <td>{{ $row->nama_ktp ?: '-' }}</td>
                                    <td>{{ $row->nik ?: '-' }}</td>
                                    <td>{{ $row->tgl_lahir?->format('d-m-Y') ?: '-' }}</td>
                                    <td>{{ $row->agama ?: '-' }}</td>
                                    <td>{{ $row->pekerjaan ?: '-' }}</td>
                                    <td>{{ $row->instansi_pensiun ?: '-' }}</td>
                                    <td>{{ $row->hp ?: '-' }}</td>
                                    <td>
                                        @if($row->nopen)
                                            <a href="{{ route('debitur.edit', ['nopen' => $row->nopen]) }}" class="btn btn-sm btn-warning">
                                                Edit
                                            </a>
                                        @else
                                            <span class="text-muted small">Nopen kosong</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($debitur->hasPages())
        <div class="mt-3">
            {{ $debitur->links('vendor.pagination.numeric-only') }}
        </div>
    @endif
</div>
@endsection
