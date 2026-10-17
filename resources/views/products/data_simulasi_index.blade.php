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

    @if($isTrialList ?? false)
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('data_simulasi.trial.list') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label for="nopen" class="form-label mb-1">Nopen</label>
                        <input
                            type="text"
                            id="nopen"
                            name="nopen"
                            class="form-control"
                            value="{{ $filters['nopen'] ?? '' }}"
                            placeholder="Cari nomor pensiun"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="nama" class="form-label mb-1">Nama</label>
                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            class="form-control"
                            value="{{ $filters['nama'] ?? '' }}"
                            placeholder="Cari nama debitur"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="nomor_hp" class="form-label mb-1">No HP</label>
                        <input
                            type="text"
                            id="nomor_hp"
                            name="nomor_hp"
                            class="form-control"
                            value="{{ $filters['nomor_hp'] ?? '' }}"
                            placeholder="Cari nomor HP"
                        >
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('data_simulasi.trial.list') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
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
                                <th>Nopen</th>
                                <th>Tgl Lahir</th>
                                <th>Usia</th>
                                <th>No HP</th>
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
                                <th>Ext Tata Laksana</th>
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
                                                @if(filled($row->nomor_pensiun))
                                                    <a href="{{ route('debitur.edit', ['nopen' => $row->nomor_pensiun]) }}" class="btn btn-sm btn-info">Debitur Info</a>
                                                @endif
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#detailRumusModal"
                                                        data-detail-rumus="{{ json_encode($row->detail_rumus ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                                                        data-detail-title="Detail Rumus Simulasi #{{ $row->id }}{{ $row->nama_debitur ? ' - ' . $row->nama_debitur : '' }}">
                                                    Detail Rumus
                                                </button>
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
                                        <td>{{ $row->nomor_pensiun ?: '-' }}</td>
                                        <td>{{ $row->tanggal_lahir?->format('d-m-Y') ?: '-' }}</td>
                                        <td>{{ $row->umur !== null ? $row->umur . ' th' : '-' }}</td>
                                        <td>{{ $row->nomor_hp !== null ? $row->nomor_hp : '-' }}</td>
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
                                                @if(filled($row->nomor_pensiun))
                                                    <a href="{{ route('debitur.edit', ['nopen' => $row->nomor_pensiun]) }}" class="btn btn-sm btn-info">Debitur Info</a>
                                                @endif
                                                <a href="{{ route('data_simulasi.idpb.upload_form', $row) }}" class="btn btn-sm btn-outline-dark">Upload IDPB</a>
                                                <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $row->id, 'focus' => 'permohonan_cif']) }}" class="btn btn-sm btn-outline-dark">Preview Permohonan CIF</a>
                                                <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $row->id, 'focus' => 'pelunasan_to_kb']) }}" class="btn btn-sm btn-outline-dark">Preview Pelunasan TO KB</a>
                                                <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $row->id, 'focus' => 'dnka_horizontal']) }}" class="btn btn-sm btn-outline-success">Preview DNKA Horizontal</a>
                                                <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $row->id, 'focus' => 'dnka_vertical']) }}" class="btn btn-sm btn-outline-success">Preview DNKA Vertical</a>
                                                <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $row->id, 'focus' => 'data_nominatif']) }}" class="btn btn-sm btn-outline-secondary">Preview Data Nominatif</a>
                                                <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $row->id, 'focus' => 'data_los_bulk']) }}" class="btn btn-sm btn-outline-secondary">Preview Data LOS Bulk</a>
                                                <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $row->id, 'focus' => 'data_rekening']) }}" class="btn btn-sm btn-outline-secondary">Preview Data Rekening</a>
                                                <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $row->id, 'focus' => 'repayment_schedule']) }}" class="btn btn-sm btn-outline-secondary">Preview Repayment Schedule</a>
                                                <a href="{{ route('excel_bundle.download', ['data_simulasi_id' => $row->id]) }}" class="btn btn-sm btn-success">Download Excel Bundle</a>
                                                <a href="{{ route('perjanjian_kredit.generate', $row) }}" class="btn btn-sm btn-outline-primary">PK Standard</a>
                                                <a href="{{ route('perjanjian_kredit.generate', $row) }}?version=kb" class="btn btn-sm btn-outline-info">PK KB Version</a>
                                                <a href="{{ route('si.generate_to', $row) }}" class="btn btn-sm btn-outline-warning">SI TO</a>
                                                <a href="{{ route('si.generate_new_topup', $row) }}" class="btn btn-sm btn-outline-warning">SI New/Topup</a>
                                                <a href="{{ route('sppk.generate', $row) }}" class="btn btn-sm btn-outline-primary">SPPK</a>
                                                <form action="{{ route('data_simulasi.back_to_trial', $row) }}" method="POST" onsubmit="return confirm('Yakin kembalikan data simulasi ini ke Trial?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-danger">Back to Trial</button>
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

@if($isTrialList ?? false)
    <div class="modal fade" id="detailRumusModal" tabindex="-1" aria-labelledby="detailRumusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailRumusModalLabel">Detail Rumus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">Rincian asal-usul nilai simulasi. Nilai perhitungan mengikuti pembulatan ke rupiah penuh.</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 22%;">Komponen</th>
                                    <th style="width: 20%;" class="text-end">Nilai</th>
                                    <th>Rumus</th>
                                </tr>
                            </thead>
                            <tbody id="detailRumusModalBody">
                                <tr>
                                    <td colspan="3" class="text-muted">Tidak ada rincian.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('detailRumusModal');
            if (!modalEl) {
                return;
            }

            var numberFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

            modalEl.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) {
                    return;
                }

                var title = button.getAttribute('data-detail-title') || 'Detail Rumus';
                var labelEl = document.getElementById('detailRumusModalLabel');
                if (labelEl) {
                    labelEl.textContent = title;
                }

                var rows = [];
                try {
                    rows = JSON.parse(button.getAttribute('data-detail-rumus') || '[]');
                } catch (e) {
                    rows = [];
                }

                var tbody = document.getElementById('detailRumusModalBody');
                tbody.innerHTML = '';

                if (!Array.isArray(rows) || rows.length === 0) {
                    var emptyRow = document.createElement('tr');
                    var emptyCell = document.createElement('td');
                    emptyCell.colSpan = 3;
                    emptyCell.className = 'text-muted';
                    emptyCell.textContent = 'Tidak ada rincian.';
                    emptyRow.appendChild(emptyCell);
                    tbody.appendChild(emptyRow);
                    return;
                }

                rows.forEach(function (row) {
                    var tr = document.createElement('tr');

                    var cellKomponen = document.createElement('td');
                    cellKomponen.textContent = row.komponen || '-';
                    tr.appendChild(cellKomponen);

                    var cellNilai = document.createElement('td');
                    cellNilai.className = 'text-end';
                    if (row.nilai_text !== undefined && row.nilai_text !== null && row.nilai_text !== '') {
                        cellNilai.textContent = row.nilai_text;
                    } else {
                        var nilai = Number(row.nilai);
                        cellNilai.textContent = isNaN(nilai) ? '-' : 'Rp ' + numberFormat.format(nilai);
                    }
                    tr.appendChild(cellNilai);

                    var cellRumus = document.createElement('td');
                    cellRumus.textContent = row.rumus || '-';
                    tr.appendChild(cellRumus);

                    tbody.appendChild(tr);
                });
            });
        });
    </script>
@endif
@endsection