@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Mapping Mail Merge</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header fw-semibold">Daftarkan Template Existing (SPPK / PK)</div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Gunakan blade template SPPK atau Perjanjian Kredit yang sudah ada. HTML/CSS tidak akan diubah — data diambil otomatis dari data simulasi &amp; pelengkap.</p>
                    <form action="{{ route('mail_merge.store_existing') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-5">
                            <label class="form-label" for="ex_name">Nama Template</label>
                            <input type="text" id="ex_name" name="name" class="form-control" placeholder="cth: SPPK Standard NBP" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="ex_document_type">Jenis Dokumen</label>
                            <select id="ex_document_type" name="document_type" class="form-select" required>
                                <option value="">Pilih...</option>
                                <option value="sppk">SPPK</option>
                                <option value="perjanjian_kredit">Perjanjian Kredit</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">Daftarkan</button>
                        </div>
                    </form>
                </div>
    </div>

    <div class="card mb-3">
        <div class="card-header fw-semibold">Buat Mapping Dari PDF Baru</div>
        <div class="card-body">
            <form action="{{ route('mail_merge.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label" for="name">Nama Template</label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="document_type">Jenis Dokumen</label>
                    <select id="document_type" name="document_type" class="form-select @error('document_type') is-invalid @enderror" required>
                        <option value="">Pilih...</option>
                        <option value="perjanjian_kredit" @selected(old('document_type') === 'perjanjian_kredit')>Perjanjian Kredit</option>
                        <option value="sppk" @selected(old('document_type') === 'sppk')>SPPK</option>
                        <option value="si" @selected(old('document_type') === 'si')>SI</option>
                        <option value="other" @selected(old('document_type') === 'other')>Lainnya</option>
                    </select>
                    @error('document_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="template_pdf">Upload PDF Template</label>
                    <input type="file" id="template_pdf" name="template_pdf" accept="application/pdf" class="form-control @error('template_pdf') is-invalid @enderror" required>
                    @error('template_pdf')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Generate View</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header fw-semibold">Daftar Mapping</div>
        <div class="card-body p-0">
            @if($templates->isEmpty())
                <div class="p-3 text-muted">Belum ada template mapping.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Dokumen</th>
                                <th>Generated View</th>
                                <th>Updated</th>
                                <th style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td>{{ $template->id }}</td>
                                    <td>{{ $template->name }}</td>
                                    <td>{{ strtoupper($template->document_type) }}</td>
                                    <td>
                                        @if($template->existing_blade_view)
                                            <span class="badge bg-success">Blade Existing</span>
                                            <code class="ms-1 small">{{ $template->existing_blade_view }}</code>
                                        @else
                                            <code class="small">{{ $template->generated_view_path ?: '-' }}</code>
                                        @endif
                                    </td>
                                    <td>{{ $template->updated_at?->format('d-m-Y H:i') ?: '-' }}</td>
                                    <td>
                                        <a href="{{ route('mail_merge.edit', $template) }}" class="btn btn-sm btn-primary">Edit Mapping</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($templates->hasPages())
        <div class="mt-3">{{ $templates->links() }}</div>
    @endif
</div>
@endsection