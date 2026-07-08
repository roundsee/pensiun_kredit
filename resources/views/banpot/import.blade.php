@extends('layouts.app')

@section('content')
<div class="container py-3" x-data="banpotImportForm()">
    <div class="row justify-content-center">
        <div class="col-xl-11">
            <div class="card mb-3">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span>Import Banpot</span>
                    <a href="{{ route('banpot.index') }}" class="btn btn-sm btn-outline-secondary">List Banpot</a>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Tahun</label>
                            <select class="form-select" x-model="selectedYear">
                                <option value="">-- Pilih Tahun --</option>
                                <template x-for="year in years" :key="year">
                                    <option :value="year" x-text="year"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <select class="form-select" x-model="selectedMonth">
                                <option value="">-- Pilih Bulan --</option>
                                <template x-for="month in months" :key="month.value">
                                    <option :value="month.value" x-text="month.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bank</label>
                            <select class="form-select" x-model="selectedBank">
                                <option value="">-- Pilih Bank --</option>
                                <template x-for="bank in banks" :key="bank">
                                    <option :value="bank" x-text="bank"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">File Excel</label>
                            <input type="file" class="form-control" accept=".xlsx,.xls" @change="handleFileChange">
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-primary" type="button" @click="previewBanpot">Import</button>
                                <button class="btn btn-success" type="button" @click="saveBanpot" :disabled="previewRows.length === 0">Simpan Banpot</button>
                                <button class="btn btn-outline-secondary" type="button" @click="resetForm">Reset</button>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0" x-show="message" x-text="message"></div>
                    <div class="alert alert-warning mt-3 mb-0" x-show="errorMessage" x-text="errorMessage"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span>Isi File Excel</span>
                    <span class="badge text-bg-secondary" x-text="previewRows.length ? `${previewRows.length} baris` : '0 baris'"></span>
                </div>
                <div class="card-body" style="max-height: 620px; overflow: auto;">
                    <template x-if="previewRows.length === 0">
                        <p class="text-muted mb-0">Belum ada data preview.</p>
                    </template>

                    <template x-if="previewRows.length > 0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 90px;">No</th>
                                        <template x-for="header in headers" :key="header">
                                            <th x-text="header"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="row in previewRows" :key="row.row_number">
                                        <tr>
                                            <td x-text="row.row_number"></td>
                                            <template x-for="header in headers" :key="`${row.row_number}-${header}`">
                                                <td x-text="displayValue(row.data?.[header])"></td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $banpotMeta = [
        'years' => $years,
        'months' => $months,
        'banks' => $banks,
        'routes' => [
            'preview' => route('banpot.preview'),
            'store' => route('banpot.store'),
        ],
    ];
@endphp
<script type="application/json" id="banpot-meta">@json($banpotMeta)</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
<script>
function banpotImportForm() {
    const meta = JSON.parse(document.getElementById('banpot-meta')?.textContent || '{}');
    const routes = meta.routes || {};
    const csrf = '{{ csrf_token() }}';

    return {
        years: meta.years || [],
        months: meta.months || [],
        banks: meta.banks || [],
        selectedYear: '',
        selectedMonth: '',
        selectedBank: '',
        selectedFile: null,
        previewRows: [],
        headers: [],
        sourceFilename: '',
        sheetName: '',
        message: '',
        errorMessage: '',

        buildHeaders() {
            return {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            };
        },

        clearStatus() {
            this.message = '';
            this.errorMessage = '';
        },

        resetPreview() {
            this.previewRows = [];
            this.headers = [];
            this.sourceFilename = '';
            this.sheetName = '';
        },

        resetForm() {
            this.selectedYear = '';
            this.selectedMonth = '';
            this.selectedBank = '';
            this.selectedFile = null;
            this.clearStatus();
            this.resetPreview();

            const fileInput = document.querySelector('input[type="file"]');
            if (fileInput) {
                fileInput.value = '';
            }
        },

        handleFileChange(event) {
            this.selectedFile = event.target.files?.[0] || null;
            this.clearStatus();
            this.resetPreview();
        },

        displayValue(value) {
            if (value === null || value === undefined || value === '') {
                return '-';
            }

            return value;
        },

        async previewBanpot() {
            this.clearStatus();

            if (!this.selectedYear || !this.selectedMonth || !this.selectedBank) {
                this.errorMessage = 'Pilih tahun, bulan, dan bank terlebih dahulu.';
                return;
            }

            if (!this.selectedFile) {
                this.errorMessage = 'Silakan pilih file Excel terlebih dahulu.';
                return;
            }

            const formData = new FormData();
            formData.append('tahun', this.selectedYear);
            formData.append('bulan', this.selectedMonth);
            formData.append('bank', this.selectedBank);
            formData.append('excel_file', this.selectedFile);

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
                this.errorMessage = data.message || 'Preview Excel gagal diproses.';
                return;
            }

            const preview = data.preview || {};
            this.headers = preview.headers || [];
            this.previewRows = preview.rows || [];
            this.sourceFilename = preview.source_filename || (this.selectedFile?.name || '');
            this.sheetName = preview.sheet_name || '';
            this.message = data.message || `Preview berhasil. ${this.previewRows.length} baris terdeteksi.`;
        },

        async saveBanpot() {
            this.clearStatus();

            if (this.previewRows.length === 0) {
                this.errorMessage = 'Lakukan import/preview Excel terlebih dahulu.';
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
                    body: JSON.stringify({
                        tahun: Number(this.selectedYear),
                        bulan: Number(this.selectedMonth),
                        bank: this.selectedBank,
                        source_filename: this.sourceFilename,
                        sheet_name: this.sheetName,
                        rows: this.previewRows,
                    }),
                });
                data = await res.json();
            } catch (err) {
                this.errorMessage = 'Kesalahan jaringan atau server: ' + err.message;
                return;
            }

            if (!res.ok) {
                this.errorMessage = data.message || 'Gagal menyimpan Banpot.';
                return;
            }

            this.message = `Banpot berhasil disimpan. ${data.rows_created} baris.`;
        },
    };
}
</script>
@endsection