@extends('layouts.app')

@section('content')
<div class="container" x-data="dataSimulasiForm()">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card mb-3">
                <div class="card-header fw-bold">Upload dan Preview PDF</div>
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('data_simulasi.list') }}" class="btn btn-sm btn-outline-secondary">Lihat Data Simulasi</a>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label">File PDF</label>
                            <input type="file" class="form-control" accept="application/pdf" @change="handleFileChange">
                        </div>
                        <div class="col-md-4 d-grid gap-2">
                            <button class="btn btn-outline-primary" type="button" @click="previewPdf">Preview PDF</button>
                            <button class="btn btn-primary" type="button" @click="saveDataSimulasi" :disabled="previewRows.length === 0">Simpan Data Simulasi</button>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0" x-show="message" x-text="message"></div>
                    <div class="alert alert-warning mt-3 mb-0" x-show="errorMessage" x-text="errorMessage"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-bold d-flex justify-content-between">
                    <span>Hasil Preview Parsing</span>
                    <span class="badge text-bg-secondary" x-text="previewRows.length ? `${previewRows.length} field` : '0 field'"></span>
                </div>
                <div class="card-body" style="max-height: 520px; overflow: auto;">
                    <template x-if="previewRows.length === 0">
                        <p class="text-muted mb-0">Belum ada data preview.</p>
                    </template>
                    <template x-if="previewRows.length > 0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Field</th>
                                    <th style="width: 35%;">Value</th>
                                    <th style="width: 35%;">Normalized</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in previewRows" :key="`${row.field_key}-${index}`">
                                    <tr>
                                        <td x-text="row.field_label"></td>
                                        <td x-text="row.field_value ?? '-' "></td>
                                        <td x-text="row.normalized_value ?? '-' "></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $dataSimulasiRoutes = [
        'preview' => route('simulation.preview_pdf_text'),
        'store' => route('data_simulasi.store'),
    ];
@endphp
<script type="application/json" id="data-simulasi-routes">@json($dataSimulasiRoutes)</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
<script>
function dataSimulasiForm() {
    const routes = JSON.parse(document.getElementById('data-simulasi-routes')?.textContent || '{}');
    const csrf = '{{ csrf_token() }}';

    return {
        selectedFile: null,
        previewRows: [],
        message: '',
        errorMessage: '',

        buildHeaders() {
            return {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            };
        },

        handleFileChange(event) {
            this.selectedFile = event.target.files?.[0] || null;
            this.previewRows = [];
            this.message = '';
            this.errorMessage = '';
        },

        clearStatus() {
            this.message = '';
            this.errorMessage = '';
        },

        async previewPdf() {
            this.clearStatus();

            if (!this.selectedFile) {
                this.errorMessage = 'Silakan pilih file PDF terlebih dahulu.';
                return;
            }

            const formData = new FormData();
            formData.append('pdf_file', this.selectedFile);

            let res;
            let data;
            try {
                res = await fetch(routes.preview, {
                    method: 'POST',
                    headers: this.buildHeaders(),
                    body: formData,
                });
                data = await res.json();
            } catch (err) {
                this.errorMessage = 'Kesalahan jaringan atau server: ' + err.message;
                return;
            }

            if (!res.ok) {
                this.errorMessage = data.message || 'Preview gagal diproses.';
                return;
            }

            this.previewRows = data.rows || [];
            this.message = `Preview berhasil. ${data.rows_count || this.previewRows.length} field terdeteksi.`;
        },

        async saveDataSimulasi() {
            this.clearStatus();

            if (this.previewRows.length === 0) {
                this.errorMessage = 'Lakukan preview PDF terlebih dahulu.';
                return;
            }

            let res;
            let data;
            try {
                res = await fetch(routes.store, {
                    method: 'POST',
                    headers: {
                        ...this.buildHeaders(),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ rows: this.previewRows }),
                });
                data = await res.json();
            } catch (err) {
                this.errorMessage = 'Kesalahan jaringan atau server: ' + err.message;
                return;
            }

            if (!res.ok) {
                this.errorMessage = data.message || 'Gagal menyimpan data simulasi.';
                return;
            }

            this.message = `Data simulasi berhasil disimpan dengan ID ${data.id}.`;
        },
    };
}
</script>
@endsection