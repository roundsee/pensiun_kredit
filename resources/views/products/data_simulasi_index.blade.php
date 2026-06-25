@extends('layouts.app')

@section('content')
<div class="container">
    <style>
        .data-simulasi-action-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
            min-width: 510px;
        }

        .data-simulasi-action-grid > a,
        .data-simulasi-action-grid > form > button {
            width: 100%;
            white-space: nowrap;
        }
    </style>

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
                                @if($isTrialList ?? false)
                                <th style="min-width: 280px;">Aksi</th>
                                <th>ID</th>
                                <th>Keterangan</th>
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
                                @else
                                <th>Nama Debitur</th>
                                <th>Tgl Lahir</th>
                                <th>Bank Tujuan</th>
                                <th>Plafond</th>
                                <th>Tenor</th>
                                <th>Total Angsuran</th>
                                <th>Asuransi</th>
                                <th style="min-width: 540px;">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataSimulasi as $row)
                                <tr>
                                    @if($isTrialList ?? false)
                                        <td>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <a href="{{ route('kb_simulasi.index', ['edit_data_simulasi' => $row->id]) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('kb_simulasi.download_pdf') }}" method="POST" target="_blank">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $row->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Download PDF</button>
                                                </form>
                                                <form action="{{ route('data_simulasi.confirm', $row) }}" method="POST" onsubmit="return confirm('Konfirmasi simulasi ini? Data akan pindah ke list Data Simulasi.')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-success">Confirm</button>
                                                </form>
                                                <form action="{{ route('data_simulasi.destroy', $row) }}" method="POST" onsubmit="return confirm('Yakin hapus trial simulasi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                        <td>{{ $row->id }}</td>
                                        <td>{{ $row->keterangan ?: '-' }}</td>
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
                                    @else
                                        <td>{{ $row->nama_debitur ?: '-' }}</td>
                                        <td>{{ $row->tanggal_lahir?->format('d-m-Y') ?: '-' }}</td>
                                        <td>{{ $row->bank_tujuan ?: '-' }}</td>
                                        <td>{{ $row->plafond !== null ? number_format((float) $row->plafond, 0, ',', '.') : '-' }}</td>
                                        <td>{{ $row->tenor !== null ? $row->tenor : '-' }}</td>
                                        <td>{{ $row->total_angsuran !== null ? number_format((float) $row->total_angsuran, 0, ',', '.') : '-' }}</td>
                                        <td>{{ $row->asuransi !== null ? number_format((float) $row->asuransi, 0, ',', '.') : '-' }}</td>
                                    @endif
                                    <td>
                                        @if(!($isTrialList ?? false))
                                            <div class="data-simulasi-action-grid">
                                                <a href="{{ route('kb_simulasi.index', ['edit_data_simulasi' => $row->id]) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('kb_simulasi.download_pdf') }}" method="POST" target="_blank">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $row->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">PDF Simulasi</button>
                                                </form>
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