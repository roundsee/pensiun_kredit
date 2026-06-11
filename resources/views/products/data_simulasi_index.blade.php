@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Data Simulasi</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('data_simulasi.index') }}" class="btn btn-primary">Upload PDF</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            @if($dataSimulasi->isEmpty())
                <div class="p-3 text-muted">Belum ada data simulasi tersimpan.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Debitur</th>
                                <th>Produk</th>
                                <th>Nomor Pensiun</th>
                                <th>Instansi</th>
                                <th>Plafond</th>
                                <th>Created</th>
                                <th style="width: 900px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataSimulasi as $row)
                                <tr>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->nama_debitur ?: '-' }}</td>
                                    <td>{{ $row->produk ?: '-' }}</td>
                                    <td>{{ $row->nomor_pensiun ?: '-' }}</td>
                                    <td>{{ $row->instansi ?: '-' }}</td>
                                    <td>{{ $row->plafond ?: '-' }}</td>
                                    <td>{{ $row->created_at?->format('d-m-Y H:i') ?: '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('kb_simulasi.index', ['edit_data_simulasi' => $row->id]) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="{{ route('data_simulasi.pelengkap.edit', $row) }}"
                                               class="btn btn-sm {{ $row->pelengkap ? 'btn-info' : 'btn-outline-info' }}">
                                                {{ $row->pelengkap ? 'Edit Pelengkap' : 'Input Pelengkap' }}
                                            </a>
                                            <a href="{{ route('data_simulasi.idpb.upload_form', $row) }}" class="btn btn-sm btn-outline-dark">Upload IDPB</a>
                                            <a href="{{ route('permohonan_cif.download_template', ['data_simulasi_id' => $row->id]) }}" class="btn btn-sm btn-outline-dark">Permohonan CIF</a>
                                            <a href="{{ route('pelunasan_to_kb.download_template', ['data_simulasi_id' => $row->id]) }}" class="btn btn-sm btn-outline-dark">Pelunasan TO KB</a>
                                            <a href="{{ route('dnka.horizontal.download_template', ['data_simulasi_id' => $row->id]) }}" class="btn btn-sm btn-outline-success">DNKA Horizontal</a>
                                            <a href="{{ route('dnka.vertical.download_template', ['data_simulasi_id' => $row->id]) }}" class="btn btn-sm btn-outline-success">DNKA Vertical</a>
                                            <a href="{{ route('datanominatif.download_template', ['data_simulasi_id' => $row->id]) }}" class="btn btn-sm btn-outline-secondary">Data Nominatif</a>
                                            <a href="{{ route('data_los_bulk.download_template', ['data_simulasi_id' => $row->id]) }}" class="btn btn-sm btn-outline-secondary">Data LOS Bulk</a>
                                            <a href="{{ route('data_rekening.download_template', ['data_simulasi_id' => $row->id]) }}" class="btn btn-sm btn-outline-secondary">Data Rekening</a>
                                            <a href="{{ route('repayment_schedule.download_template', ['data_simulasi_id' => $row->id]) }}" class="btn btn-sm btn-outline-secondary">Repayment Schedule</a>
                                            <a href="{{ route('perjanjian_kredit.generate', $row) }}" class="btn btn-sm btn-outline-primary">PK Standard</a>
                                            <a href="{{ route('perjanjian_kredit.generate', $row) }}?version=kb" class="btn btn-sm btn-outline-info">PK KB Version</a>
                                            <a href="{{ route('si.generate_to', $row) }}" class="btn btn-sm btn-outline-warning">SI TO</a>
                                            <a href="{{ route('si.generate_new_topup', $row) }}" class="btn btn-sm btn-outline-warning">SI New/Topup</a>
                                            <a href="{{ route('sppk.generate', $row) }}" class="btn btn-sm btn-outline-primary">SPPK</a>
                                            <form action="{{ route('data_simulasi.destroy', $row) }}" method="POST" onsubmit="return confirm('Yakin hapus data simulasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
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