@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ $listTitle ?? 'Data Simulasi' }}</h1>
        <div></div>
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
                                @if($isTrialList ?? false)
                                <th>Keterangan</th>
                                @endif
                                <th>Nama Debitur</th>
                                <th>Tgl Lahir</th>
                                <th>Usia</th>
                                <th>Gaji</th>
                                <th>Instansi</th>
                                <th>Bank Asal</th>
                                <th>Bank Tujuan</th>
                                <th>Rate</th>
                                <th>Admin Angsuran</th>
                                <th>Angsuran</th>
                                <th>Plafond</th>
                                <th>Total Angsuran</th>
                                <th>Administrasi</th>
                                <th>Provisi</th>
                                <th>Asuransi</th>
                                <th>Blokir</th>
                                <th>Sisa Gaji Setelah Pengajuan</th>
                                <th>Terima Bersih</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataSimulasi as $row)
                                <tr>
                                    <td>{{ $row->id }}</td>
                                    @if($isTrialList ?? false)
                                        <td>{{ $row->keterangan ?: '-' }}</td>
                                    @endif
                                    <td>{{ $row->nama_debitur ?: '-' }}</td>
                                    <td>{{ $row->tanggal_lahir?->format('d-m-Y') ?: '-' }}</td>
                                    <td>{{ $row->umur !== null ? $row->umur . ' th' : '-' }}</td>
                                    <td>{{ $row->gaji_pensiun !== null ? number_format((float) $row->gaji_pensiun, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $row->instansi ?: '-' }}</td>
                                    <td>{{ $row->bank_asal ?: '-' }}</td>
                                    <td>{{ $row->bank_tujuan ?: '-' }}</td>
                                    <td>{{ $row->rate_percent_override !== null ? $row->rate_percent_override . '%' : '-' }}</td>
                                    <td>{{ $row->admin_angsuran_percent_override !== null ? $row->admin_angsuran_percent_override . '%' : '-' }}</td>
                                    <td>{{ $row->angsuran !== null ? number_format((float) $row->angsuran, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $row->plafond !== null ? number_format((float) $row->plafond, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $row->total_angsuran !== null ? number_format((float) $row->total_angsuran, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $row->administrasi !== null ? number_format((float) $row->administrasi, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $row->provisi !== null ? number_format((float) $row->provisi, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $row->asuransi !== null ? number_format((float) $row->asuransi, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $row->blokir_angsuran !== null ? $row->blokir_angsuran : '-' }}</td>
                                    <td>{{ $row->sisa_gaji_akhir !== null ? number_format((float) $row->sisa_gaji_akhir, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $row->terima_bersih !== null ? number_format((float) $row->terima_bersih, 0, ',', '.') : '-' }}</td>
                                    <td>
                                        @if($isTrialList ?? false)
                                            <form action="{{ route('data_simulasi.confirm', $row) }}" method="POST" onsubmit="return confirm('Konfirmasi simulasi ini? Data akan pindah ke list Data Simulasi.')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">Confirm</button>
                                            </form>
                                        @else
                                            <span class="badge {{ ($row->status === 'trial') ? 'text-bg-warning' : 'text-bg-success' }}">
                                                {{ $row->status ?: 'confirmed' }}
                                            </span>
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

    @if($dataSimulasi->hasPages())
        <div class="mt-3">
            {{ $dataSimulasi->links() }}
        </div>
    @endif
</div>
@endsection