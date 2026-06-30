@extends('layouts.app')

@section('content')
<style>
    .kb-sheet-wrap {
        border: 1px solid #d0d7de;
        border-radius: 0.5rem;
        overflow: hidden;
        background: #fff;
    }
    .kb-sheet-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }
    .kb-sheet-table th,
    .kb-sheet-table td {
        border: 1px solid #d8dee4;
        padding: 0.45rem 0.6rem;
        vertical-align: middle;
    }
    .kb-sheet-table thead th {
        background: #eef3f8;
        font-weight: 700;
    }
    .kb-cell-label {
        width: 44%;
        background: #fafcff;
        font-weight: 600;
    }
    .kb-cell-value {
        width: 56%;
    }
    .kb-sheet-output {
        background: #f7fbf7;
        font-weight: 600;
    }
    .kb-sheet-section td {
        background: #f2f5f9;
        font-weight: 700;
    }
    .kb-sheet-input {
        min-width: 260px;
        background: #fff1db;
        border-color: #f0c48a;
    }
    .kb-sheet-input:focus {
        background: #fff8ec;
        border-color: #d99a4e;
        box-shadow: 0 0 0 0.2rem rgba(217, 154, 78, 0.18);
    }
</style>
<div class="container" x-data="kbGoalSeeker()">
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card mb-3">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span>Goal Seeker Simulasi KB</span>
                    <a href="{{ route('kb_simulasi.index') }}" class="btn btn-sm btn-outline-secondary">Kembali ke Simulasi</a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3" x-show="message" x-text="message"></div>
                    <div class="alert alert-danger mb-3" x-show="errorMessage" x-text="errorMessage"></div>

                    <div class="alert alert-secondary">
                        1) Isi data seperti Simulasi. Perhitungan standar akan otomatis muncul sampai Terima Bersih.<br>
                        2) Setelah itu set <strong>Target</strong> dan <strong>Parameter yang Diubah</strong>, lalu klik <strong>Cari Kombinasi</strong>.
                    </div>

                    <div class="kb-sheet-wrap">
                        <table class="kb-sheet-table">
                            <tbody>
                                <template x-for="row in excelRows" :key="row.cell">
                                    <tr :class="row.type === 'section' ? 'kb-sheet-section' : ''">
                                        <td class="kb-cell-label" x-text="row.label"></td>
                                        <td class="kb-cell-value">
                                            <template x-if="row.type === 'select'">
                                                <select class="form-select form-select-sm kb-sheet-input" x-model="form[row.key]" :disabled="isInputDisabled(row)">
                                                    <option value="" x-show="row.allowEmpty">Pilih</option>
                                                    <template x-for="item in getRowOptions(row)" :key="row.cell + '_' + item">
                                                        <option :value="item" x-text="item"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="row.type === 'text'">
                                                <input type="text" class="form-control form-control-sm kb-sheet-input" x-model="form[row.key]" :disabled="isInputDisabled(row)">
                                            </template>
                                            <template x-if="row.type === 'date'">
                                                <input type="date" class="form-control form-control-sm kb-sheet-input" x-model="form[row.key]" :disabled="isInputDisabled(row)">
                                            </template>
                                            <template x-if="row.type === 'number'">
                                                <input type="number" step="any" class="form-control form-control-sm kb-sheet-input" x-model="form[row.key]" :disabled="isInputDisabled(row)">
                                            </template>
                                            <template x-if="row.type === 'integer'">
                                                <input type="number" step="1" class="form-control form-control-sm kb-sheet-input" x-model="form[row.key]" :disabled="isInputDisabled(row)">
                                            </template>
                                            <template x-if="row.type === 'output'">
                                                <div class="kb-sheet-output p-1 rounded" x-text="getRowDisplayValue(row)"></div>
                                            </template>
                                            <template x-if="row.type === 'blank'">
                                                <div class="text-muted">-</div>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="small text-muted mt-2" x-show="isCalculatingStandard">Menghitung otomatis...</div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label">Tenor Min</label>
                            <input type="number" class="form-control kb-sheet-input" x-model.number="form.tenor_min" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tenor Max (opsional)</label>
                            <input type="number" class="form-control kb-sheet-input" x-model.number="form.tenor_max" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Parameter yang Diubah (pilih 1 atau 2)</label>
                            <div class="d-flex flex-wrap gap-3 pt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="param-rate" value="rate_percent_override" :checked="isParamSelected('rate_percent_override')" @change="toggleParam('rate_percent_override')" :disabled="!permissions.can_edit_pricing && !isParamSelected('rate_percent_override')">
                                    <label class="form-check-label" for="param-rate">Rate</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="param-tenor" value="tenor" :checked="isParamSelected('tenor')" @change="toggleParam('tenor')">
                                    <label class="form-check-label" for="param-tenor">Tenor</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="param-plafond" value="plafond" :checked="isParamSelected('plafond')" @change="toggleParam('plafond')">
                                    <label class="form-check-label" for="param-plafond">Plafon</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="param-admin" value="admin_angsuran_percent_override" :checked="isParamSelected('admin_angsuran_percent_override')" @change="toggleParam('admin_angsuran_percent_override')" :disabled="!permissions.can_edit_pricing && !isParamSelected('admin_angsuran_percent_override')">
                                    <label class="form-check-label" for="param-admin">Adm Angsuran (%)</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Target (Pilihan)</label>
                            <select class="form-select" x-model="form.target_field">
                                <option value="angsuran">Angsuran</option>
                                <option value="plafond">Plafon</option>
                                <option value="terima_bersih">Terima Bersih</option>
                                <option value="sisa_gaji_akhir">Sisa Gaji Akhir</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nilai Target</label>
                            <input type="number" class="form-control" placeholder="Contoh: 1000000" x-model.number="form.target_value">
                        </div>
                        <div class="col-12 form-text">Contoh: target terima bersih, tenor dan plafon tetap dari hasil standar, parameter diubah cukup rate.</div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-primary" type="button" @click="cari()" :disabled="isSearching">
                            <span x-text="isSearching ? 'Mencari kombinasi...' : 'Cari Kombinasi' "></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card" x-show="hasil">
                <div class="card-header fw-bold">Hasil Rekomendasi</div>
                <div class="card-body" x-show="hasil">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="small text-muted">Rate Terpilih</div>
                            <div class="fw-semibold" x-text="formatRate(getSelectedRate())"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Tenor</div>
                            <div class="fw-semibold" x-text="hasil.tenor + ' bulan'"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Plafond</div>
                            <div class="fw-semibold" x-text="formatCurrency(hasil.plafond)"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="small text-muted">Kombinasi Dicek</div>
                            <div class="fw-semibold" x-text="checkedCount"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Target</div>
                            <div class="fw-semibold" x-text="targetSummary"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Parameter Diubah</div>
                            <div class="fw-semibold" x-text="paramSummary"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Total Angsuran</div>
                            <div class="fw-semibold" x-text="formatCurrency(hasil.total_angsuran)"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Sisa Gaji Akhir</div>
                            <div class="fw-semibold" x-text="formatCurrency(hasil.sisa_gaji_akhir)"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">Terima Bersih</div>
                            <div class="fw-semibold" x-text="formatCurrency(hasil.terima_bersih)"></div>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-success" @click="gunakanKeSimulasi()">
                                Gunakan Hasil ke Form Simulasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $goalSeekerPermissions = [
        'can_edit_pricing' => (bool) ($canEditPricing ?? false),
    ];
    $goalSeekerRoutes = [
        'search' => route('kb_simulasi.goal_seek'),
        'calculate' => route('kb_simulasi.calculate'),
        'simulasi' => route('kb_simulasi.index'),
    ];
@endphp
<script type="application/json" id="kb-goal-options">{!! json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/json" id="kb-goal-permissions">{!! json_encode($goalSeekerPermissions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/json" id="kb-goal-routes">{!! json_encode($goalSeekerRoutes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
<script>
function kbGoalSeeker() {
    const options = JSON.parse(document.getElementById('kb-goal-options')?.textContent || '{}');
    const permissions = JSON.parse(document.getElementById('kb-goal-permissions')?.textContent || '{}');
    const routes = JSON.parse(document.getElementById('kb-goal-routes')?.textContent || '{}');
    const csrf = '{{ csrf_token() }}';

    const today = new Date().toISOString().slice(0, 10);

    return {
        options,
        permissions,
        isSearching: false,
        isCalculatingStandard: false,
        autoCalculateTimer: null,
        message: '',
        errorMessage: '',
        checkedCount: 0,
        hasil: null,
        standardResult: {},
        selectedInputs: {},
        targetSummary: '',
        paramSummary: '',
        form: {
            produk: (options.produk && options.produk.length > 0) ? options.produk[0] : 'Platinum',
            jenis_pensiun: 'Sendiri',
            mutasi: 'Non Mutasi',
            bank_asal: (options.bank_asal && options.bank_asal.length > 0) ? options.bank_asal[0] : '',
            bank_tujuan: (options.bank_tujuan && options.bank_tujuan.length > 0) ? options.bank_tujuan[0] : 'KB',
            tanggal_simulasi: today,
            nama_debitur: '',
            tanggal_lahir: '',
            nomor_pensiun: '',
            instansi: 'TASPEN',
            gaji_pensiun: '',
            angsuran_lainnya: 0,
            blokir_angsuran: '1',
            pelunasan: 0,
            rate_percent_override: '',
            admin_angsuran_percent_override: '',
            nama_marketing: '',
            kode_area: (options.area && options.area.length > 0) ? options.area[0] : '',
            tenor_min: 1,
            tenor_max: '',
            tenor: '',
            plafond: '',
            target_field: 'angsuran',
            target_value: '',
            adjustable_parameters: ['rate_percent_override'],
        },
        mutasiOptions: ['Mutasi', 'Non Mutasi'],
        blokirOptions: ['1', '2', '3', '4', '5'],
        instansiOptions: ['TASPEN', 'ASABRI'],
        excelRows: [
            { cell: 'E10', label: 'Produk', type: 'select', key: 'produk', optionsKey: 'produk', allowEmpty: true },
            { cell: 'E11', label: 'Jenis Pensiun', type: 'select', key: 'jenis_pensiun', optionsKey: 'jenis_pensiun' },
            { cell: 'E12', label: 'Mutasi', type: 'select', key: 'mutasi', optionsKey: 'mutasi' },
            { cell: 'E13', label: 'Bank Asal', type: 'select', key: 'bank_asal', optionsKey: 'bank_asal', allowEmpty: true },
            { cell: 'E14', label: 'Bank Tujuan', type: 'select', key: 'bank_tujuan', optionsKey: 'bank_tujuan', allowEmpty: true },
            { cell: 'E16', label: 'INPUT DATA', type: 'section' },
            { cell: 'E17', label: 'Tanggal Simulasi', type: 'date', key: 'tanggal_simulasi' },
            { cell: 'E18', label: 'Nama Debitur', type: 'text', key: 'nama_debitur' },
            { cell: 'E19', label: 'Tanggal Lahir', type: 'date', key: 'tanggal_lahir' },
            { cell: 'E20', label: 'Umur', type: 'output', key: 'umur_text', format: 'text' },
            { cell: 'E21', label: 'Nomor Pensiun', type: 'text', key: 'nomor_pensiun' },
            { cell: 'E22', label: 'Instansi', type: 'select', key: 'instansi', optionsKey: 'instansi' },
            { cell: 'E23', label: 'Gaji Pensiun', type: 'integer', key: 'gaji_pensiun' },
            { cell: 'E24', label: 'Angsuran Lainnya', type: 'integer', key: 'angsuran_lainnya' },
            { cell: 'E25', label: 'Sisa Gaji saat pengajuan', type: 'output', key: 'sisa_gaji_saat_pengajuan', format: 'currency' },
            { cell: 'E26', label: 'Tenor Max', type: 'output', key: 'tenor_max', format: 'months' },
            { cell: 'E26A', label: 'Rate (%) Override', type: 'number', key: 'rate_percent_override', onlyRoleCanEditPricing: true },
            { cell: 'E26B', label: 'Adm Angsuran (%) Override', type: 'number', key: 'admin_angsuran_percent_override', onlyRoleCanEditPricing: true },
            { cell: 'E28', label: 'Tenor', type: 'integer', key: 'tenor' },
            { cell: 'E27', label: 'Plafond Max', type: 'output', key: 'plafond_max', format: 'currency' },
            { cell: 'E29', label: 'Plafond', type: 'integer', key: 'plafond' },
            { cell: 'E30', label: 'Blokir', type: 'select', key: 'blokir_angsuran', optionsKey: 'blokir' },
            { cell: 'E31', label: 'ANGSURAN', type: 'output', key: 'angsuran', format: 'currency' },
            { cell: 'E32', label: 'Biaya Adm Angs', type: 'output', key: 'biaya_adm_angs', format: 'currency' },
            { cell: 'E33', label: 'Total Angsuran', type: 'output', key: 'total_angsuran', format: 'currency' },
            { cell: 'E34', label: 'RINCIAN PEMBIAYAAN', type: 'section' },
            { cell: 'E35', label: 'PROVISI', type: 'output', key: 'provisi', format: 'currency' },
            { cell: 'E36', label: 'ADMINISTRASI', type: 'output', key: 'administrasi', format: 'currency' },
            { cell: 'E37', label: 'ASURANSI', type: 'output', key: 'asuransi', format: 'currency' },
            { cell: 'E39', label: 'BLOKIR AMOUNT', type: 'output', key: 'amount_blokir_angsuran', format: 'currency' },
            { cell: 'E41', label: 'TATA LAKSANA', type: 'output', key: 'tata_laksana', format: 'currency' },
            { cell: 'E42', label: 'PELUNASAN', type: 'integer', key: 'pelunasan' },
            { cell: 'E43', label: 'Nama Marketing', type: 'text', key: 'nama_marketing' },
            { cell: 'E44', label: 'Kode Area', type: 'select', key: 'kode_area', optionsKey: 'area', allowEmpty: true },
            { cell: 'E51', label: 'TOTAL BIAYA', type: 'output', key: 'total_biaya', format: 'currency' },
            { cell: 'E52', label: 'SISA GAJI AKHIR', type: 'output', key: 'sisa_gaji_akhir', format: 'currency' },
            { cell: 'E53', label: 'TERIMA BERSIH', type: 'output', key: 'terima_bersih', format: 'currency' },
        ],

        init() {
            const watchedPaths = [
                'produk', 'jenis_pensiun', 'mutasi', 'bank_asal', 'bank_tujuan',
                'nama_debitur', 'tanggal_simulasi', 'tanggal_lahir', 'nomor_pensiun', 'instansi',
                'gaji_pensiun', 'angsuran_lainnya', 'blokir_angsuran', 'rate_percent_override',
                'admin_angsuran_percent_override', 'tenor', 'plafond', 'pelunasan', 'nama_marketing', 'kode_area',
            ];

            watchedPaths.forEach((key) => {
                this.$watch(`form.${key}`, () => this.scheduleAutoCalculate());
            });

            this.scheduleAutoCalculate();
        },

        isInputDisabled(row) {
            return !!row.onlyRoleCanEditPricing && !this.permissions.can_edit_pricing;
        },

        getRowOptions(row) {
            if (row.optionsKey === 'mutasi') return this.mutasiOptions;
            if (row.optionsKey === 'blokir') return this.blokirOptions;
            if (row.optionsKey === 'instansi') return this.instansiOptions;
            if (row.optionsKey === 'area') return Array.isArray(this.options.area) ? this.options.area : [];
            if (row.optionsKey === 'bank_asal') return Array.isArray(this.options.bank_asal) ? this.options.bank_asal : [];
            if (row.optionsKey === 'bank_tujuan') return Array.isArray(this.options.bank_tujuan) ? this.options.bank_tujuan : [];
            if (row.optionsKey === 'produk') return Array.isArray(this.options.produk) ? this.options.produk : [];
            if (row.optionsKey === 'jenis_pensiun') return Array.isArray(this.options.jenis_pensiun) ? this.options.jenis_pensiun : [];
            return [];
        },

        formatDate(value) {
            if (!value) return '-';
            const d = new Date(value + 'T00:00:00');
            if (Number.isNaN(d.getTime())) return value;
            return d.toLocaleDateString('id-ID');
        },

        getRowDisplayValue(row) {
            const value = this.standardResult ? this.standardResult[row.key] : null;
            if (value === null || value === undefined || value === '') {
                return '-';
            }
            if (row.format === 'currency') return this.formatCurrency(value);
            if (row.format === 'months') return `${value} bulan`;
            if (row.format === 'date') return this.formatDate(value);
            return String(value);
        },

        isParamSelected(param) {
            return this.form.adjustable_parameters.includes(param);
        },

        toggleParam(param) {
            const existing = this.form.adjustable_parameters;
            if (existing.includes(param)) {
                this.form.adjustable_parameters = existing.filter((item) => item !== param);
                return;
            }

            if (existing.length >= 2) {
                this.errorMessage = 'Parameter yang diubah maksimal 2.';
                return;
            }

            this.form.adjustable_parameters = [...existing, param];
        },

        getTargetLabel() {
            const map = {
                angsuran: 'Angsuran',
                plafond: 'Plafon',
                terima_bersih: 'Terima Bersih',
                sisa_gaji_akhir: 'Sisa Gaji Akhir',
            };
            return map[this.form.target_field] || this.form.target_field;
        },

        getParamLabel(param) {
            const map = {
                rate_percent_override: 'Rate',
                tenor: 'Tenor',
                plafond: 'Plafon',
                admin_angsuran_percent_override: 'Adm Angsuran (%)',
            };
            return map[param] || param;
        },

        formatCurrency(value) {
            const amount = Number(value || 0);
            return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
        },

        formatRate(value) {
            const rate = Number(value);
            if (!Number.isFinite(rate)) {
                return '-';
            }
            return rate.toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' %';
        },

        buildStandardPayload() {
            return {
                produk: this.form.produk,
                jenis_pensiun: this.form.jenis_pensiun,
                mutasi: this.form.mutasi,
                bank_asal: this.form.bank_asal,
                bank_tujuan: this.form.bank_tujuan,
                nama_debitur: this.form.nama_debitur || '-',
                tanggal_simulasi: this.form.tanggal_simulasi,
                tanggal_lahir: this.form.tanggal_lahir,
                nomor_pensiun: this.form.nomor_pensiun || '-',
                instansi: this.form.instansi,
                gaji_pensiun: this.form.gaji_pensiun,
                angsuran_lainnya: this.form.angsuran_lainnya,
                blokir_angsuran: this.form.blokir_angsuran,
                pelunasan: this.form.pelunasan,
                rate_percent_override: this.form.rate_percent_override,
                admin_angsuran_percent_override: this.form.admin_angsuran_percent_override,
                tenor: this.form.tenor,
                plafond: this.form.plafond,
                nama_marketing: this.form.nama_marketing || '-',
                kode_area: this.form.kode_area || '-',
            };
        },

        scheduleAutoCalculate() {
            if (this.autoCalculateTimer) {
                clearTimeout(this.autoCalculateTimer);
            }
            this.autoCalculateTimer = setTimeout(() => {
                this.hitungStandar();
            }, 450);
        },

        async hitungStandar() {
            if (!this.form.produk || !this.form.jenis_pensiun || !this.form.bank_tujuan || !this.form.tanggal_simulasi || !this.form.tanggal_lahir) {
                this.standardResult = {};
                return;
            }

            this.isCalculatingStandard = true;
            try {
                const response = await fetch(routes.calculate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(this.buildStandardPayload()),
                });
                const data = await response.json();
                if (!response.ok) {
                    this.standardResult = {};
                    return;
                }
                this.standardResult = data.data || null;
            } catch (error) {
                this.standardResult = {};
            } finally {
                this.isCalculatingStandard = false;
            }
        },

        getSelectedRate() {
            if (this.selectedInputs && this.selectedInputs.rate_percent_override !== undefined && this.selectedInputs.rate_percent_override !== null && this.selectedInputs.rate_percent_override !== '') {
                return this.selectedInputs.rate_percent_override;
            }
            if (this.form.rate_percent_override !== undefined && this.form.rate_percent_override !== null && this.form.rate_percent_override !== '') {
                return this.form.rate_percent_override;
            }
            return null;
        },

        gunakanKeSimulasi() {
            if (!this.hasil) {
                this.errorMessage = 'Belum ada hasil Goal Seeker untuk dipakai ke Simulasi.';
                return;
            }

            const payload = {
                produk: this.form.produk,
                jenis_pensiun: this.form.jenis_pensiun,
                mutasi: this.form.mutasi,
                bank_asal: this.form.bank_asal,
                bank_tujuan: this.form.bank_tujuan,
                tanggal_simulasi: this.form.tanggal_simulasi,
                tanggal_lahir: this.form.tanggal_lahir,
                instansi: this.form.instansi,
                gaji_pensiun: this.form.gaji_pensiun,
                angsuran_lainnya: this.form.angsuran_lainnya,
                blokir_angsuran: this.form.blokir_angsuran,
                pelunasan: this.form.pelunasan,
                tenor: this.hasil.tenor ?? this.form.tenor,
                plafond: this.hasil.plafond ?? this.form.plafond,
                rate_percent_override: this.getSelectedRate(),
                admin_angsuran_percent_override: (this.selectedInputs && this.selectedInputs.admin_angsuran_percent_override !== undefined)
                    ? this.selectedInputs.admin_angsuran_percent_override
                    : this.form.admin_angsuran_percent_override,
            };

            try {
                sessionStorage.setItem('kb_goal_seeker_transfer', JSON.stringify(payload));
                window.location.href = routes.simulasi;
            } catch (error) {
                this.errorMessage = 'Gagal memindahkan data ke form Simulasi.';
            }
        },

        async cari() {
            this.isSearching = true;
            this.message = '';
            this.errorMessage = '';
            this.hasil = null;
            this.selectedInputs = {};
            this.targetSummary = '';
            this.paramSummary = '';

            if (this.form.adjustable_parameters.length < 1 || this.form.adjustable_parameters.length > 2) {
                this.errorMessage = 'Pilih 1 atau 2 parameter yang diubah.';
                this.isSearching = false;
                return;
            }

            try {
                const response = await fetch(routes.search, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await response.json();
                if (!response.ok) {
                    this.errorMessage = data.message || 'Gagal mencari kombinasi.';
                    this.checkedCount = Number(data.checked_count || 0);
                    return;
                }

                this.message = data.message || 'Kombinasi ditemukan.';
                this.checkedCount = Number(data.checked_count || 0);
                this.targetSummary = `${this.getTargetLabel()} target ${this.formatCurrency(this.form.target_value)} ; hasil ${this.formatCurrency(data.target.result_value)} ; selisih ${this.formatCurrency(data.target.difference)}`;
                this.paramSummary = (data.selected && Array.isArray(data.selected.adjustable_parameters))
                    ? data.selected.adjustable_parameters.map((item) => this.getParamLabel(item)).join(' + ')
                    : this.form.adjustable_parameters.map((item) => this.getParamLabel(item)).join(' + ');
                this.selectedInputs = (data.selected && data.selected.inputs) ? data.selected.inputs : {};
                this.hasil = {
                    ...data.data,
                };
            } catch (error) {
                this.errorMessage = 'Terjadi kesalahan saat mencari kombinasi.';
            } finally {
                this.isSearching = false;
            }
        },
    };
}
</script>
@endsection
