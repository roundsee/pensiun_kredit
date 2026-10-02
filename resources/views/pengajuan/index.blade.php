@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-1">Data Pengajuan</h1>
            <div class="text-muted">Pilih <strong>Info</strong> untuk menangani debitur dan pengajuan-pinjamannya.</div>
        </div>
        <a href="{{ route('data_simulasi.list') }}" class="btn btn-outline-secondary">List Data Simulasi Lama</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('data_pengajuan.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="nopen" class="form-label mb-1">Nopen</label>
                    <input type="text" id="nopen" name="nopen" class="form-control"
                           value="{{ $filters['nopen'] }}" placeholder="Cari nomor pensiun">
                </div>
                <div class="col-md-3">
                    <label for="nama" class="form-label mb-1">Nama</label>
                    <input type="text" id="nama" name="nama" class="form-control"
                           value="{{ $filters['nama'] }}" placeholder="Cari nama debitur">
                </div>
                <div class="col-md-2">
                    <label for="nomor_hp" class="form-label mb-1">No HP</label>
                    <input type="text" id="nomor_hp" name="nomor_hp" class="form-control"
                           value="{{ $filters['nomor_hp'] }}" placeholder="Cari nomor HP">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label mb-1">Status</label>
                    <select id="status" name="status" class="form-select">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('data_pengajuan.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($dataSimulasi->isEmpty())
                <div class="p-3 text-muted">Tidak ada data simulasi yang cocok dengan filter.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Debitur</th>
                                <th>Nopen</th>
                                <th>Tgl Lahir</th>
                                <th>No HP</th>
                                <th>Plafond</th>
                                <th>Tenor</th>
                                <th>Total Angsuran</th>
                                <th>Status</th>
                                <th style="min-width: 190px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataSimulasi as $row)
                                <tr>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->nama_debitur ?: '-' }}</td>
                                    <td>{{ $row->nomor_pensiun ?: '-' }}</td>
                                    <td>{{ $row->tanggal_lahir?->format('d-m-Y') ?: '-' }}</td>
                                    <td>{{ $row->nomor_hp ?: '-' }}</td>
                                    <td>{{ $row->plafond !== null ? number_format((float) $row->plafond, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $row->tenor !== null ? $row->tenor : '-' }}</td>
                                    <td>{{ $row->total_angsuran !== null ? number_format((float) $row->total_angsuran, 0, ',', '.') : '-' }}</td>
                                    <td>
                                        @if($row->status === 'trial')
                                            <span class="badge text-bg-warning">Trial</span>
                                        @else
                                            <span class="badge text-bg-success">Confirmed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('data_pengajuan.info', $row) }}" class="btn btn-sm btn-info">Info</a>
                                            <form action="{{ route('kb_simulasi.download_pdf') }}" method="POST" target="_blank">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $row->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Download PDF</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($dataSimulasi->hasPages())
        <div class="mt-3">
            {{ $dataSimulasi->links() }}
        </div>
    @endif
</div>
@endsection
