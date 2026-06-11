@extends('layouts.app')

@section('content')
<div class="container" x-data="simulationForm()" @submit.prevent>
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header fw-bold">Simulasi PDF ke Pengajuan</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Produk</label>
                            <select class="form-select" x-model="selectedProduct" @change="fetchAccounts">
                                <option value="">-- Pilih Produk --</option>
                                <template x-for="product in products" :key="product.id">
                                    <option :value="product.id" x-text="product.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nasabah</label>
                            <select class="form-select" x-model="nasabahId">
                                <option value="">-- Pilih Nasabah --</option>
                                <template x-for="user in users" :key="user.id">
                                    <option :value="user.id" x-text="`${user.name} (${user.email})`"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pendana</label>
                            <select class="form-select" x-model="lenderId">
                                <option value="">-- Pilih Pendana --</option>
                                <template x-for="lender in lenders" :key="lender.id">
                                    <option :value="lender.id" x-text="lender.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No Pinjaman</label>
                            <input class="form-control" x-model="loanNumber" placeholder="AUTO/CHN-...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bunga (%)</label>
                            <input type="number" step="0.01" class="form-control" x-model.number="interestRate">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fallback Plafond</label>
                            <input type="number" step="0.01" class="form-control" x-model.number="fallbackPlafond">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fallback Tenor</label>
                            <input type="number" class="form-control" x-model.number="fallbackTenor">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">File PDF (text-based)</label>
                            <input type="file" class="form-control" accept="application/pdf" @change="handleFileChange">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="d-grid gap-2 w-100">
                                <button class="btn btn-outline-primary" type="button" @click="previewPdf">Upload &amp; Preview PDF</button>
                                <button class="btn btn-primary" type="button" @click="importPdf">Simpan Batch</button>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0" x-show="message" x-text="message"></div>
                    <div class="alert alert-warning mt-3 mb-0" x-show="errorMessage" x-text="errorMessage"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-bold">Preview Field Parsing</div>
                <div class="card-body" style="max-height: 420px; overflow: auto;">
                    <template x-if="previewRows.length === 0">
                        <p class="text-muted mb-0">Belum ada preview.</p>
                    </template>
                    <template x-if="previewRows.length > 0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Value</th>
                                    <th>Normalized</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in previewRows" :key="idx">
                                    <tr>
                                        <td x-text="row.field_label"></td>
                                        <td x-text="row.field_value ?? '-'"></td>
                                        <td x-text="row.normalized_value ?? '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </template>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-bold d-flex justify-content-between">
                    <span>Map ke Payload Pengajuan</span>
                    <span class="badge text-bg-secondary" x-text="batchId ? `Batch: ${batchId}` : 'Batch: -'"></span>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-outline-secondary" type="button" @click="mapToProposal" :disabled="!batchId || !selectedProduct">Map Batch ke Proposal</button>
                        <button class="btn btn-success" type="button" @click="applyToProposal" :disabled="!mappedPayload || !selectedProduct || !nasabahId || !lenderId">Apply ke Pengajuan</button>
                    </div>

                    <div class="mb-2 fw-semibold">Payload Proposal</div>
                    <pre class="bg-light border rounded p-2" style="max-height: 280px; overflow: auto;" x-text="mappedPayload ? JSON.stringify(mappedPayload, null, 2) : 'Belum ada payload.'"></pre>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header fw-bold">Rincian Financial Produk</div>
                <div class="card-body">
                    <template x-if="accounts.length === 0">
                        <p class="text-muted mb-0">Pilih produk untuk menampilkan rincian financial.</p>
                    </template>
                    <template x-if="accounts.length > 0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Tipe Hitung</th>
                                    <th>Default</th>
                                    <th>Nilai Simulasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="account in accounts" :key="account.id">
                                    <tr>
                                        <td x-text="account.item_name"></td>
                                        <td x-text="account.calculation_type"></td>
                                        <td x-text="account.default_value"></td>
                                        <td x-text="formatCurrency(calculate(account))"></td>
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

<script type="application/json" id="simulation-products">@json($products ?? [])</script>
<script type="application/json" id="simulation-users">@json($users ?? [])</script>
<script type="application/json" id="simulation-lenders">@json($lenders ?? [])</script>
@php
    $simulationRoutes = [
        'preview' => route('simulation.preview_pdf_text'),
        'import' => route('simulation.import_pdf_text'),
        'map' => url('/simulation/batches'),
        'propose' => route('loans.propose'),
        'accounts' => url('/simulation/accounts'),
    ];
@endphp
<script type="application/json" id="simulation-routes">@json($simulationRoutes)</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
<script>
function simulationForm() {
    const routes = JSON.parse(document.getElementById('simulation-routes')?.textContent || '{}');
    const csrf = '{{ csrf_token() }}';

    return {
        products: JSON.parse(document.getElementById('simulation-products')?.textContent || '[]'),
        users: JSON.parse(document.getElementById('simulation-users')?.textContent || '[]'),
        lenders: JSON.parse(document.getElementById('simulation-lenders')?.textContent || '[]'),
        selectedProduct: '',
        nasabahId: '',
        lenderId: '',
        loanNumber: '',
        interestRate: 12,
        fallbackPlafond: 0,
        fallbackTenor: 0,
        plafond: 10000000,
        accounts: [],
        selectedFile: null,
        previewRows: [],
        batchId: '',
        mappedPayload: null,
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
        },

        clearStatus() {
            this.message = '';
            this.errorMessage = '';
        },

        fetchAccounts() {
            if (!this.selectedProduct) {
                this.accounts = [];
                return;
            }
            fetch(`${routes.accounts}?product_id=${this.selectedProduct}`)
                .then(res => res.json())
                .then(data => {
                    this.accounts = data.accounts || [];
                });
        },

        async previewPdf() {
            this.clearStatus();
            if (!this.selectedFile) {
                this.errorMessage = 'Silakan pilih file PDF terlebih dahulu.';
                return;
            }

            const formData = new FormData();
            formData.append('pdf_file', this.selectedFile);

            let res, data;
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
            if (this.previewRows.length === 0) {
                this.errorMessage = 'PDF berhasil dibaca tetapi tidak ada baris field yang terdeteksi. Pastikan format PDF sesuai.';
                return;
            }
            this.message = `Preview berhasil. ${data.rows_count || this.previewRows.length} baris terdeteksi.`;
        },

        async importPdf() {
            this.clearStatus();
            if (!this.selectedFile) {
                this.errorMessage = 'Silakan pilih file PDF terlebih dahulu.';
                return;
            }

            const formData = new FormData();
            formData.append('pdf_file', this.selectedFile);
            if (this.selectedProduct) {
                formData.append('product_id', this.selectedProduct);
            }

            let res, data;
            try {
                res = await fetch(routes.import, {
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
                this.errorMessage = data.message || 'Import PDF gagal diproses.';
                return;
            }

            this.batchId = data.simulation_batch_id;
            this.message = `Import berhasil. Batch ${this.batchId}, ${data.rows_created} baris.`;
        },

        async mapToProposal() {
            this.clearStatus();
            if (!this.batchId || !this.selectedProduct) {
                this.errorMessage = 'Batch dan produk wajib dipilih.';
                return;
            }

            let res, data;
            try {
                res = await fetch(`${routes.map}/${this.batchId}/map-proposal`, {
                    method: 'POST',
                    headers: {
                        ...this.buildHeaders(),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ product_id: Number(this.selectedProduct) }),
                });
                data = await res.json();
            } catch (err) {
                this.errorMessage = 'Kesalahan jaringan atau server: ' + err.message;
                return;
            }
            if (!res.ok) {
                this.errorMessage = data.message || 'Mapping batch gagal.';
                return;
            }

            this.mappedPayload = data.proposal_payload;
            if (this.mappedPayload?.amount_plafond) {
                this.plafond = Number(this.mappedPayload.amount_plafond);
            }
            this.message = 'Mapping batch ke payload proposal berhasil.';
        },

        buildLoanNumber() {
            if (this.loanNumber && this.loanNumber.trim() !== '') {
                return this.loanNumber.trim();
            }
            const now = new Date();
            const stamp = `${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}${String(now.getHours()).padStart(2, '0')}${String(now.getMinutes()).padStart(2, '0')}${String(now.getSeconds()).padStart(2, '0')}`;
            return `AUTO-${stamp}`;
        },

        async applyToProposal() {
            this.clearStatus();
            if (!this.mappedPayload) {
                this.errorMessage = 'Payload belum tersedia, lakukan mapping terlebih dahulu.';
                return;
            }
            if (!this.selectedProduct || !this.nasabahId || !this.lenderId) {
                this.errorMessage = 'Produk, Nasabah, dan Pendana wajib dipilih.';
                return;
            }

            const payload = {
                loan_number: this.buildLoanNumber(),
                product_id: Number(this.selectedProduct),
                nasabah_id: Number(this.nasabahId),
                lender_id: Number(this.lenderId),
                amount_plafond: Number(this.mappedPayload.amount_plafond || this.fallbackPlafond || 0),
                interest_rate: Number(this.interestRate || 0),
                debtor_data: this.mappedPayload.debtor_data || {},
                submission_data: {
                    ...(this.mappedPayload.submission_data || {}),
                },
                financial_data: this.mappedPayload.financial_data || {},
            };

            if ((!payload.submission_data.tenor || Number(payload.submission_data.tenor) <= 0) && Number(this.fallbackTenor) > 0) {
                payload.submission_data.tenor = Number(this.fallbackTenor);
            }

            if (payload.amount_plafond <= 0) {
                this.errorMessage = 'Amount plafond belum terbaca. Isi fallback plafond atau pastikan nilai plafon ada di PDF.';
                return;
            }

            const res = await fetch(routes.propose, {
                method: 'POST',
                headers: {
                    ...this.buildHeaders(),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok) {
                this.errorMessage = data.message || 'Apply ke pengajuan gagal.';
                return;
            }

            this.message = `Pengajuan berhasil dibuat. Loan ID: ${data.loan?.id ?? '-'}`;
            this.loanNumber = payload.loan_number;
        },

        calculate(account) {
            const defaultValue = Number(account.default_value || 0);
            if (account.calculation_type === 'percentage') {
                return (Number(this.plafond || 0) * defaultValue) / 100;
            }
            return defaultValue;
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
        }
    }
}
</script>
@endsection
