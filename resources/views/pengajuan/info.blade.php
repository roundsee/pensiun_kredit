@extends('layouts.app')

@section('content')
@php
    $halamanTabs = ['debitur' => 'Data Debitur', 'pengajuan' => 'Pengajuan', 'dokumen' => 'Dokumen', 'surat' => 'Surat'];
    $tabAktif = array_key_exists(request('tab'), $halamanTabs) ? request('tab') : 'debitur';
    $isTrial = $simulasi->status === 'trial';
@endphp

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-1">
                {{ $simulasi->nama_debitur ?: 'Tanpa Nama' }}
                @if($isTrial)
                    <span class="badge text-bg-warning align-middle">Trial</span>
                @else
                    <span class="badge text-bg-success align-middle">Confirmed</span>
                @endif
            </h1>
            <div class="text-muted">
                Nopen <strong>{{ $simulasi->nomor_pensiun ?: '-' }}</strong>
                &middot; ID Simulasi {{ $simulasi->id }}
                @if($simulasi->bank_tujuan)
                    &middot; {{ $simulasi->bank_tujuan }}
                @endif
            </div>
        </div>
        <a href="{{ route('data_pengajuan.index') }}" class="btn btn-outline-secondary">Kembali ke List</a>
    </div>

    {{-- Search di dalam form supaya admin bisa ganti debitur/pengajuan tanpa
         balik ke halaman list. --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('data_pengajuan.cari') }}" class="row g-2 align-items-end">
                <div class="col-md-9">
                    <label for="q" class="form-label mb-1">Ganti Debitur / Pengajuan</label>
                    <input type="text" id="q" name="q" class="form-control"
                           value="{{ request('q') }}" placeholder="Cari berdasarkan nopen, NIK, nama, atau no HP">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Buka</button>
                    <a href="{{ route('data_pengajuan.info', $simulasi) }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            Ada {{ $errors->count() }} field yang perlu diperbaiki. Form otomatis pindah ke tab yang bermasalah.
        </div>
    @endif

    <ul class="nav nav-tabs mb-3" id="pengajuan-tabs" role="tablist">
        @foreach($halamanTabs as $key => $judul)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $tabAktif === $key ? 'active' : '' }}"
                        id="halaman-tab-{{ $key }}"
                        data-bs-toggle="tab"
                        data-bs-target="#halaman-pane-{{ $key }}"
                        type="button" role="tab"
                        aria-controls="halaman-pane-{{ $key }}"
                        aria-selected="{{ $tabAktif === $key ? 'true' : 'false' }}">{{ $judul }}</button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content" id="pengajuan-tab-content">

        {{-- ============ Data Debitur ============ --}}
        <div class="tab-pane fade {{ $tabAktif === 'debitur' ? 'show active' : '' }}"
             id="halaman-pane-debitur" role="tabpanel" aria-labelledby="halaman-tab-debitur">
            @if($debitur === null)
                <div class="alert alert-warning mb-0">
                    Simulasi ini belum punya nomor pensiun, jadi profil debitur belum bisa dibuat.
                    Isi <strong>Nopen</strong> di halaman edit simulasi terlebih dahulu.
                    @if(filled($simulasi->nomor_pensiun) === false)
                        <a href="{{ route('kb_simulasi.index', ['edit_data_simulasi' => $simulasi->id]) }}" class="alert-link ms-1">Edit Simulasi</a>
                    @endif
                </div>
            @else
                <form method="POST" action="{{ route('debitur.update', ['nopen' => $debitur->nopen]) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="return_to" value="data_pengajuan.info">

                    @include('debitur._tabs', [
                        'debiturTabs' => $debiturTabs,
                        'debiturFieldMap' => $debiturFieldMap,
                        'debiturTabsId' => 'debitur-tabs',
                    ])

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Data Debitur</button>
                        <a href="{{ route('debitur.edit', ['nopen' => $debitur->nopen]) }}" class="btn btn-outline-secondary">Buka Halaman Debitur Info</a>
                    </div>
                </form>
            @endif
        </div>

        {{-- ============ Pengajuan ============ --}}
        <div class="tab-pane fade {{ $tabAktif === 'pengajuan' ? 'show active' : '' }}"
             id="halaman-pane-pengajuan" role="tabpanel" aria-labelledby="halaman-tab-pengajuan">

            <div class="card mb-3">
                <div class="card-header bg-light">Aksi Pengajuan</div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('kb_simulasi.index', ['edit_data_simulasi' => $simulasi->id]) }}" class="btn btn-warning">Edit Simulasi</a>

                        <form action="{{ route('kb_simulasi.download_pdf') }}" method="POST" target="_blank">
                            @csrf
                            <input type="hidden" name="id" value="{{ $simulasi->id }}">
                            <button type="submit" class="btn btn-outline-danger">Download PDF</button>
                        </form>

                        @if($isTrial)
                            <form action="{{ route('data_simulasi.confirm', $simulasi) }}" method="POST"
                                  onsubmit="return confirm('Konfirmasi simulasi ini? Data akan pindah ke list Data Simulasi.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success">Confirm</button>
                            </form>
                        @else
                            <form action="{{ route('data_simulasi.back_to_trial', $simulasi) }}" method="POST"
                                  onsubmit="return confirm('Yakin kembalikan data simulasi ini ke Trial?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger">Back to Trial</button>
                            </form>
                        @endif

                        @if($isTrial)
                            <form action="{{ route('data_simulasi.destroy', $simulasi) }}" method="POST"
                                  onsubmit="return confirm('Yakin hapus trial simulasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            @foreach($ringkasan as $group)
                <div class="card mb-3">
                    <div class="card-header bg-light">{{ $group['legend'] }}</div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($group['items'] as $item)
                                <div class="col-sm-6 col-lg-4">
                                    <div class="text-muted small">{{ $item['label'] }}</div>
                                    <div class="fw-semibold">{{ $item['value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ============ Dokumen ============ --}}
        <div class="tab-pane fade {{ $tabAktif === 'dokumen' ? 'show active' : '' }}"
             id="halaman-pane-dokumen" role="tabpanel" aria-labelledby="halaman-tab-dokumen">

            <div class="card mb-3">
                <div class="card-header bg-light">Data &amp; Unggah</div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('data_simulasi.pelengkap.edit', $simulasi) }}"
                           class="btn {{ $simulasi->pelengkap ? 'btn-info' : 'btn-outline-info' }}">
                            {{ $simulasi->pelengkap ? 'Edit Pelengkap' : 'Input Pelengkap' }}
                        </a>
                        <a href="{{ route('data_simulasi.idpb.upload_form', $simulasi) }}" class="btn btn-outline-dark">Upload IDPB</a>
                        <a href="{{ route('data_simulasi.permohonan_cif.upload_form', $simulasi) }}" class="btn btn-outline-dark">Upload Permohonan CIF</a>
                        <a href="{{ route('data_simulasi.pelunasan_to_kb.upload_form', $simulasi) }}" class="btn btn-outline-dark">Upload Pelunasan TO KB</a>
                        <a href="{{ route('excel_bundle.download', ['data_simulasi_id' => $simulasi->id]) }}" class="btn btn-success">Download Excel Bundle</a>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">Preview Isi Excel Bundle</div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $simulasi->id, 'focus' => 'permohonan_cif']) }}" class="btn btn-sm btn-outline-dark">Permohonan CIF</a>
                        <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $simulasi->id, 'focus' => 'pelunasan_to_kb']) }}" class="btn btn-sm btn-outline-dark">Pelunasan TO KB</a>
                        <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $simulasi->id, 'focus' => 'dnka_horizontal']) }}" class="btn btn-sm btn-outline-success">DNKA Horizontal</a>
                        <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $simulasi->id, 'focus' => 'dnka_vertical']) }}" class="btn btn-sm btn-outline-success">DNKA Vertical</a>
                        <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $simulasi->id, 'focus' => 'data_nominatif']) }}" class="btn btn-sm btn-outline-secondary">Data Nominatif</a>
                        <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $simulasi->id, 'focus' => 'data_los_bulk']) }}" class="btn btn-sm btn-outline-secondary">Data LOS Bulk</a>
                        <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $simulasi->id, 'focus' => 'data_rekening']) }}" class="btn btn-sm btn-outline-secondary">Data Rekening</a>
                        <a href="{{ route('excel_bundle.preview', ['data_simulasi_id' => $simulasi->id, 'focus' => 'repayment_schedule']) }}" class="btn btn-sm btn-outline-secondary">Repayment Schedule</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ Surat ============ --}}
        <div class="tab-pane fade {{ $tabAktif === 'surat' ? 'show active' : '' }}"
             id="halaman-pane-surat" role="tabpanel" aria-labelledby="halaman-tab-surat">
            <div class="card">
                <div class="card-header bg-light">Surat &amp; Dokumen Perjanjian</div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('perjanjian_kredit.generate', $simulasi) }}" class="btn btn-outline-primary">PK Standard</a>
                        <a href="{{ route('perjanjian_kredit.generate', $simulasi) }}?version=kb" class="btn btn-outline-info">PK KB Version</a>
                        <a href="{{ route('si.generate_to', $simulasi) }}" class="btn btn-outline-warning">SI TO</a>
                        <a href="{{ route('si.generate_new_topup', $simulasi) }}" class="btn btn-outline-warning">SI New/Topup</a>
                        <a href="{{ route('sppk.generate', $simulasi) }}" class="btn btn-outline-primary">SPPK</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('debitur._wilayah_js')
@endsection
