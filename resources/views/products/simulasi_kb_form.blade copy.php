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
    }
</style>
<div class="container" x-data="kbSimulasiForm()">
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card mb-3">
                <div class="card-header fw-bold">Simulasi</div>
                <div class="card-body">
                    <div class="alert alert-info mb-3" x-show="shouldShowMessages() && message" x-text="message"></div>
                    <div class="alert alert-danger mb-3" x-show="shouldShowMessages() && errorMessage" x-text="errorMessage"></div>
                    <div class="alert alert-warning mb-3" x-show="shouldShowMessages() && limitWarning" x-text="limitWarning"></div>

                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('data_simulasi.list') }}" class="btn btn-sm btn-outline-secondary">Lihat Data Simulasi</a>
                    </div>

                    <div class="kb-sheet-wrap">
                        <table class="kb-sheet-table">
                            <tbody>
                                <template x-for="row in getRenderableRows()" :key="row.cell">
                                    <tr :class="row.type === 'section' ? 'kb-sheet-section' : ''">
                                        <td class="kb-cell-label" x-text="row.label"></td>
                                        <td class="kb-cell-value">
                                            <template x-if="row.type === 'select'">
                                                <select class="form-select form-select-sm kb-sheet-input" x-model="form[row.key]" :disabled="isInputDisabled(row)" @keydown.enter.prevent="onInputEnter(row, $event)">
                                                    <option value="" x-show="row.allowEmpty" :selected="isEmptyOptionSelected(row)">Pilih</option>
                                                    <template x-for="item in getRowOptions(row)" :key="row.cell + '_' + item">
                                                        <option :value="item" :selected="isOptionSelected(row, item)" x-text="item"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="row.type === 'text'">
                                                <input type="text" class="form-control form-control-sm kb-sheet-input" x-model="form[row.key]" :disabled="isInputDisabled(row)" @change="onInputCommit(row, $event)" @blur="onInputCommit(row, $event)" @keydown.enter.prevent="onInputEnter(row, $event)">
                                            </template>
                                            <template x-if="row.type === 'date'">
                                                <input type="date" class="form-control form-control-sm kb-sheet-input" x-model="form[row.key]" :disabled="isInputDisabled(row)" @change="onInputCommit(row, $event)" @blur="onInputCommit(row, $event)" @keydown.enter.prevent="onInputEnter(row, $event)">
                                            </template>
                                            <template x-if="row.type === 'number'">
                                                <input type="number" class="form-control form-control-sm kb-sheet-input" step="any" x-model.lazy="form[row.key]" :disabled="isInputDisabled(row)" @input="onInputLive(row, $event)" @change="onInputCommit(row, $event)" @blur="onInputCommit(row, $event)" @keydown.enter.prevent="onInputEnter(row, $event)">
                                            </template>
                                            <template x-if="row.type === 'integer'">
                                                <input type="number" class="form-control form-control-sm kb-sheet-input" step="1" x-model.lazy="form[row.key]" :disabled="isInputDisabled(row)" @input="onInputLive(row, $event)" @change="onInputCommit(row, $event)" @blur="onInputCommit(row, $event)" @keydown.enter.prevent="onInputEnter(row, $event)">
                                            </template>
                                            <template x-if="row.type === 'output'">
                                                <div class="kb-sheet-output p-1 rounded" x-text="getRowDisplayValue(row)"></div>
                                            </template>
                                            <template x-if="row.type === 'section'">
                                                <div class="text-muted">&nbsp;</div>
                                            </template>
                                            <template x-if="row.type === 'blank'">
                                                <div class="text-muted">-</div>
                                            </template>
                                            <template x-if="getFieldError(row.key)">
                                                <div class="text-danger small mt-1" x-text="getFieldError(row.key)"></div>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-outline-primary" type="button" @click="hitung(false)">Hitung Ulang (Manual)</button>
                        <button class="btn btn-primary" type="button" @click="simpan" :disabled="!hasil || isSaving" x-text="isSaving ? 'Menyimpan...' : 'Simpan ke Data Simulasi'"></button>
                        <button class="btn btn-outline-danger" type="button" @click="downloadPdf" :disabled="!hasil || isDownloading" x-text="isDownloading ? 'Menyiapkan PDF...' : 'Download PDF'"></button>
                    </div>
                    <div class="small text-muted mt-2" x-show="isCalculating">Menghitung otomatis...</div>

                </div>
            </div>
        </div>
    </div>
</div>

@php
    $kbInsuranceConfigs = $insuranceConfigs ?? ['default_percent' => 0];
    $kbInitialData = $initialData ?? null;
    $kbPermissions = [
        'role' => $userRole ?? 'marketing',
        'can_edit_pricing' => (bool) ($canEditPricing ?? false),
    ];
    $kbSimulasiRoutes = [
        'calculate' => route('kb_simulasi.calculate'),
        'store' => route('kb_simulasi.store'),
        'downloadPdf' => route('kb_simulasi.download_pdf'),
        'updateBase' => url('/data-simulasi'),
    ];
@endphp
<script type="application/json" id="kb-simulasi-options">{!! json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/json" id="kb-product-structs">{!! json_encode($productStructs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/json" id="kb-insurance-configs">{!! json_encode($kbInsuranceConfigs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/json" id="kb-insurance-rates">{!! json_encode($insuranceRates ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/json" id="kb-initial-data">{!! json_encode($kbInitialData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/json" id="kb-permissions">{!! json_encode($kbPermissions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/json" id="kb-simulasi-routes">{!! json_encode($kbSimulasiRoutes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
<script>
function kbSimulasiForm() {
    const options = JSON.parse(document.getElementById('kb-simulasi-options')?.textContent || '{}');
    const productStructs = JSON.parse(document.getElementById('kb-product-structs')?.textContent || '{}');
    const insuranceConfigs = JSON.parse(document.getElementById('kb-insurance-configs')?.textContent || '{}');
    const insuranceRates = JSON.parse(document.getElementById('kb-insurance-rates')?.textContent || '{}');
    const initialData = JSON.parse(document.getElementById('kb-initial-data')?.textContent || 'null');
    const permissions = JSON.parse(document.getElementById('kb-permissions')?.textContent || '{}');
    const routes = JSON.parse(document.getElementById('kb-simulasi-routes')?.textContent || '{}');
    const csrf = '{{ csrf_token() }}';
    
    return {
        options,
        productStructs,
        insuranceConfigs,
        insuranceRates,
        permissions,
        editDataSimulasiId: initialData && initialData.id ? Number(initialData.id) : null,
        form: {
            produk: '',
            jenis_pensiun: 'Sendiri',
            mutasi: 'Non Mutasi',
            bank_tujuan: '',
            nama_debitur: '',
            tanggal_simulasi: new Date().toISOString().slice(0, 10),
            tanggal_lahir: '',
            nomor_pensiun: '',
            instansi: 'TASPEN',
            gaji_pensiun: '',
            angsuran_lainnya: '',
            blokir_angsuran: '1',
            rate_percent_override: '',
            admin_angsuran_percent_override: '',
            tenor: '',
            plafond: '',
            pelunasan: '',
            nama_marketing: '',
            kode_area: '',
        },
        mutasiOptions: ['Mutasi', 'Non Mutasi'],
        blokirOptions: ['1', '2', '3'],
        instansiOptions: ['TASPEN', 'ASABRI'],
        excelRows: [
            { cell: 'E10', label: 'Produk', type: 'select', key: 'produk', optionsKey: 'produk', allowEmpty: true },
            { cell: 'E11', label: 'Jenis Pensiun', type: 'select', key: 'jenis_pensiun', optionsKey: 'jenis_pensiun' },
            { cell: 'E12', label: 'Mutasi', type: 'select', key: 'mutasi', optionsKey: 'mutasi' },
            { cell: 'E13', label: 'Bank Asal', type: 'output', key: 'bank_asal', format: 'text', staticValue: 'BANK BUKOPIN' },
            { cell: 'E14', label: 'Bank Tujuan', type: 'select', key: 'bank_tujuan', optionsKey: 'bank_tujuan', allowEmpty: true },
            { cell: 'E15', label: '', type: 'blank' },
            { cell: 'E16', label: 'INPUT DATA', type: 'section' },
            { cell: 'E17', label: 'Tanggal Simulasi', type: 'date', key: 'tanggal_simulasi' },
            { cell: 'E18', label: 'Nama Debitur', type: 'text', key: 'nama_debitur' },
            { cell: 'E19', label: 'Tanggal Lahir', type: 'date', key: 'tanggal_lahir' },
            { cell: 'E20', label: 'Umur', type: 'output', key: 'umur_text', format: 'text' },
            { cell: 'E21', label: 'Nomor Pensiun', type: 'text', key: 'nomor_pensiun' },
            { cell: 'E22', label: 'Instansi', type: 'select', key: 'instansi', optionsKey: 'instansi' },
            { cell: 'E23', label: 'Gaji Pensiun', type: 'integer', key: 'gaji_pensiun' },
            { cell: 'E24', label: 'Angsuran Lainnya (sisa gaji)', type: 'integer', key: 'angsuran_lainnya' },
            { cell: 'E25', label: 'Sisa Gaji saat pengajuan', type: 'output', key: 'sisa_gaji_saat_pengajuan', format: 'currency' },
            { cell: 'E26', label: 'Tenor Max', type: 'output', key: 'tenor_max', format: 'months' },
            { cell: 'E26A', label: 'Rate (%) Override', type: 'integer', key: 'rate_percent_override', onlyRoleCanEditPricing: true },
            { cell: 'E26B', label: 'Adm Angsuran (%) Override', type: 'integer', key: 'admin_angsuran_percent_override', onlyRoleCanEditPricing: true },
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
            { cell: 'E38', label: 'Extra Premi', type: 'output', key: 'extra_premi', format: 'currency' },
            { cell: 'E39', label: 'BLOKIR AMOUNT', type: 'output', key: 'amount_blokir_angsuran', format: 'currency' },
            { cell: 'E40', label: '', type: 'blank' },
            { cell: 'E41', label: 'TATA LAKSANA', type: 'output', key: 'tata_laksana', format: 'currency' },
            { cell: 'E42', label: 'PELUNASAN', type: 'integer', key: 'pelunasan' },
            { cell: 'E43', label: 'Nama Marketing', type: 'text', key: 'nama_marketing' },
            { cell: 'E44', label: 'Kode Area', type: 'select', key: 'kode_area', optionsKey: 'area', allowEmpty: true },
            { cell: 'E45', label: '', type: 'blank' },
            { cell: 'E46', label: 'USIA LUNAS', type: 'output', key: 'usia_lunas_text', format: 'text' },
            { cell: 'E47', label: 'TGL PERMOHONAN', type: 'output', key: 'tgl_permohonan', format: 'date' },
            { cell: 'E48', label: '', type: 'blank' },
            { cell: 'E49', label: 'TGL LUNAS', type: 'output', key: 'tgl_lunas', format: 'date' },
            { cell: 'E50', label: '', type: 'blank' },
            { cell: 'E51', label: 'TOTAL BIAYA', type: 'output', key: 'total_biaya', format: 'currency' },
            { cell: 'E52', label: 'SISA GAJI AKHIR', type: 'output', key: 'sisa_gaji_akhir', format: 'currency' },
            { cell: 'E53', label: 'TERIMA BERSIH', type: 'output', key: 'terima_bersih', format: 'currency' },
            { cell: 'E54', label: 'TERIMA BERSIH', type: 'output', key: 'terima_bersih', format: 'currency' },
        ],
        hasil: null,
        hasilDisplay: [],
        limits: null,
        umurRealtimeText: '-',
        tenorMaxText: '-',
        plafondMaxText: '-',
        realtimeTenorMaxValue: 0,
        calcRequestSeq: 0,
        calcInFlightSignature: '',
        calcLastCompletedSignature: '',
        calcAbortController: null,
        enableAutoServerCalc: false,
        isCalculating: false,
        isSaving: false,
        isDownloading: false,
        autoCalculateTimer: null,
        message: '',
        errorMessage: '',
        limitWarning: '',
        fieldMessages: {},

        init() {
            this.applyInitialData();
            this.applyInitialDefaults();
            this.syncAllSelectValues();

            const watchedPaths = [
                'form.produk', 'form.jenis_pensiun', 'form.mutasi', 'form.bank_tujuan',
                'form.nama_debitur', 'form.tanggal_simulasi', 'form.tanggal_lahir',
                'form.nomor_pensiun', 'form.instansi', 'form.gaji_pensiun',
                'form.angsuran_lainnya', 'form.blokir_angsuran', 'form.rate_percent_override',
                'form.admin_angsuran_percent_override', 'form.tenor', 'form.plafond',
                'form.pelunasan', 'form.nama_marketing', 'form.kode_area',
            ];

            watchedPaths.forEach((path) => {
                this.$watch(path, () => { this.onFormChanged(); });
            });

            this.$watch('form.produk', () => { this.updateRateDefaults(); });
            this.$watch('form.jenis_pensiun', () => { this.updateRateDefaults(); });

            this.$nextTick(() => {
                this.applyInitialData();
                this.applyInitialDefaults();
                this.updateRateDefaults();
                this.syncAllSelectValues();
                this.onFormChanged();

                setTimeout(() => {
                    this.updateRateDefaults();
                    this.syncAllSelectValues();
                }, 0);
            });
        },

        applyInitialData() {
            if (!initialData || !initialData.id) return;

            Object.keys(this.form).forEach((key) => {
                if (Object.prototype.hasOwnProperty.call(initialData, key) && initialData[key] !== null && initialData[key] !== undefined) {
                    const integerFields = ['gaji_pensiun', 'angsuran_lainnya', 'plafond', 'pelunasan', 'tenor', 'rate_percent_override', 'admin_angsuran_percent_override'];
                    if (integerFields.includes(key)) {
                        // PAKSA BULAT: Menggunakan Math.round untuk menghilangkan .000000 saat load DB
                        this.form[key] = Math.round(Number(initialData[key]));
                    } else {
                        this.form[key] = initialData[key];
                    }
                }
            });

            if (initialData.blokir_angsuran !== null && initialData.blokir_angsuran !== undefined) {
                this.form.blokir_angsuran = String(initialData.blokir_angsuran);
            }
        },

        applyInitialDefaults() {
            const jenisOptions = Array.isArray(this.options.jenis_pensiun) ? this.options.jenis_pensiun : [];
            const sendiriOption = jenisOptions.find((item) => String(item).trim().toLowerCase() === 'sendiri');
            const jenisDefault = sendiriOption || jenisOptions[0] || 'Sendiri';

            const instansiOptions = Array.isArray(this.instansiOptions) ? this.instansiOptions : [];
            const taspenOption = instansiOptions.find((item) => String(item).trim().toLowerCase() === 'taspen');
            const instansiDefault = taspenOption || instansiOptions[0] || 'TASPEN';

            const blokirOptions = Array.isArray(this.blokirOptions) ? this.blokirOptions : [];
            const blokirSatu = blokirOptions.find((item) => String(item).trim() === '1');
            const blokirDefault = blokirSatu || blokirOptions[0] || '1';

            if (!this.form.jenis_pensiun || !jenisOptions.includes(this.form.jenis_pensiun)) {
                this.form.jenis_pensiun = jenisDefault;
            }

            if (!this.form.instansi || !instansiOptions.includes(this.form.instansi)) {
                this.form.instansi = instansiDefault;
            }

            if (!this.form.blokir_angsuran || !blokirOptions.includes(String(this.form.blokir_angsuran))) {
                this.form.blokir_angsuran = String(blokirDefault);
            }

            if (!this.form.tanggal_simulasi || String(this.form.tanggal_simulasi).trim() === '') {
                this.form.tanggal_simulasi = new Date().toISOString().slice(0, 10);
            }
            
            const productKey = `${this.form.produk}-${this.form.jenis_pensiun}`;
            if (this.form.produk && this.productStructs[productKey]) {
                const struct = this.productStructs[productKey];
                
                // FORCE ROUND: Pastikan dikonversi menjadi integer murni saat inisiasi awal komponen
                if ((this.form.rate_percent_override === '' || this.form.rate_percent_override === null) && struct.rate_percent !== undefined) {
                    this.form.rate_percent_override = Math.round(Number(struct.rate_percent * 100));
                }
                
                if ((this.form.admin_angsuran_percent_override === '' || this.form.admin_angsuran_percent_override === null) && struct.admin_angsuran_percent !== undefined) {
                    this.form.admin_angsuran_percent_override = Math.round(Number(struct.admin_angsuran_percent * 100));
                }
            }
        },

        updateRateDefaults() {
            const productKey = `${this.form.produk}-${this.form.jenis_pensiun}`;
            if (this.form.produk && this.productStructs && this.productStructs[productKey]) {
                const struct = this.productStructs[productKey];
                
                if ((this.form.rate_percent_override === null || this.form.rate_percent_override === '' || typeof this.form.rate_percent_override === 'undefined') && struct.rate_percent !== undefined) {
                    this.form.rate_percent_override = Math.round(Number(struct.rate_percent * 100));
                }
                
                if ((this.form.admin_angsuran_percent_override === null || this.form.admin_angsuran_percent_override === '' || typeof this.form.admin_angsuran_percent_override === 'undefined') && struct.admin_angsuran_percent !== undefined) {
                    this.form.admin_angsuran_percent_override = Math.round(Number(struct.admin_angsuran_percent * 100));
                }
            }
        },

        onFormChanged() {
            this.applyInitialDefaults();
            this.syncAllSelectValues();
            this.recalculateRealtimeAge();
            this.recalculateRealtimeTenorMax();
            this.recalculateRealtimePlafondMax();
            if (typeof this.recalculateClientSide === 'function') this.recalculateClientSide();
            if (typeof this.scheduleAutoCalculate === 'function') this.scheduleAutoCalculate();
        },

        normalizeOptionValue(value) {
            return String(value ?? '').trim().toLowerCase();
        },

        resolvePreferredSelectValue(rowKey, optionsList) {
            const preferredMap = {
                jenis_pensiun: 'sendiri',
                instansi: 'taspen',
                blokir_angsuran: '1',
                mutasi: 'non mutasi',
            };
            const preferred = preferredMap[rowKey];
            if (!preferred) return null;
            return optionsList.find((item) => this.normalizeOptionValue(item) === preferred) ?? null;
        },

        resolveInsuranceRatePercent(product, tenor) {
            //console.log("Resolving insurance rate for product:", product, "tenor:", tenor);
            if (!product || tenor === null || tenor === undefined || tenor === '') {
                return (this.insuranceConfigs.default_percent || 0) / 100;
            }
            const tenorValue = Number(tenor);
            if (!Number.isFinite(tenorValue) || tenorValue <= 0) {
                return (this.insuranceConfigs.default_percent || 0) / 100;
            }
            const rates = Array.isArray(this.insuranceRates[product]) ? this.insuranceRates[product] : [];
            if (rates.length === 0) {
                return (this.insuranceConfigs.default_percent || 0) / 100;
            }
            const matched = rates.find((item) => Number(item.tenor) >= tenorValue);
            if (matched) {
                return (Number(matched.premium_per_million) || 0) / 1000;
            }
            const last = rates[rates.length - 1];
            return last ? (Number(last.premium_per_million) || 0) / 1000 : (this.insuranceConfigs.default_percent || 0) / 100;
        },

        syncSelectValue(row) {
            if (!row || row.type !== 'select' || !row.key) return;
            console.log("Syncing select value for row key:", row.key);
            const optionsList = this.getRowOptions(row) || [];
            if (optionsList.length === 0) return;

            const current = this.form[row.key];
            const currentNormalized = this.normalizeOptionValue(current);
            console.log("Current value:", current, "Normalized:", currentNormalized, "Options:", optionsList);
            const matched = optionsList.find((item) => this.normalizeOptionValue(item) === currentNormalized) ?? null;

            if (matched !== null) {
                this.form[row.key] = matched;
                return;
            }
            if (row.key === 'produk') return;

            const preferred = this.resolvePreferredSelectValue(row.key, optionsList);
            if (preferred !== null) {
                this.form[row.key] = preferred;
                return;
            }
            this.form[row.key] = row.allowEmpty ? '' : optionsList[0];
        },

        syncAllSelectValues() {
            this.excelRows.forEach((row) => this.syncSelectValue(row));
        },

        isOptionSelected(row, item) {
            if (!row || row.type !== 'select' || !row.key) return false;
            return this.normalizeOptionValue(this.form[row.key]) === this.normalizeOptionValue(item);
        },

        isEmptyOptionSelected(row) {
            if (!row || row.type !== 'select' || !row.allowEmpty || !row.key) return false;
            const current = this.form[row.key];
            return current === null || current === undefined || String(current).trim() === '';
        },

        onInputLive(row, event) {
            if (!row || !row.key) return;
            if (row.type === 'integer' && event?.target?.value) {
                // FORCE INTEGER: Potong langsung saat user mengetik
                this.form[row.key] = Math.round(Number(event.target.value));
            } else {
                this.form[row.key] = event?.target?.value ?? this.form[row.key];
            }
            this.recalculateRealtimeAge();
            this.recalculateRealtimeTenorMax();
            this.recalculateRealtimePlafondMax();
        },

        onInputCommit(row, event) {
            if (row && row.key) {
                if (row.type === 'integer' && event?.target?.value) {
                    this.form[row.key] = Math.round(Number(event.target.value));
                } else {
                    this.form[row.key] = event?.target?.value ?? this.form[row.key];
                }
            }
            this.onFormChanged();
        },

        onInputEnter(row, event) {
            if (row && row.key) {
                if (row.type === 'integer' && event?.target?.value) {
                    this.form[row.key] = Math.round(Number(event.target.value));
                } else {
                    this.form[row.key] = event?.target?.value ?? this.form[row.key];
                }
            }
            this.onFormChanged();

            if (this.autoCalculateTimer) {
                clearTimeout(this.autoCalculateTimer);
                this.autoCalculateTimer = null;
            }
            if (typeof this.hitung === 'function') this.hitung(true);
        },

        getRealtimeSisaGajiSaatPengajuan() {
            const gajiPensiun = Number(this.form.gaji_pensiun || 0);
            const angsuranLainnya = Number(this.form.angsuran_lainnya || 0);
            if (!Number.isFinite(gajiPensiun) || !Number.isFinite(angsuranLainnya)) return 0;
            return Math.max(0, Math.round(gajiPensiun - angsuranLainnya));
        },

        recalculateRealtimeAge() {
            if (!this.form.tanggal_lahir) {
                this.umurRealtimeText = '-';
                return;
            }
            const birth = new Date(this.form.tanggal_lahir + 'T00:00:00');
            if (Number.isNaN(birth.getTime())) {
                this.umurRealtimeText = '-';
                return;
            }
            const referenceDate = this.form.tanggal_simulasi
                ? new Date(this.form.tanggal_simulasi + 'T00:00:00')
                : new Date();

            if (Number.isNaN(referenceDate.getTime())) {
                this.umurRealtimeText = '-';
                return;
            }

            let years = referenceDate.getFullYear() - birth.getFullYear();
            let months = referenceDate.getMonth() - birth.getMonth();
            if (referenceDate.getDate() < birth.getDate()) months -= 1;
            if (months < 0) {
                years -= 1;
                months += 12;
            }
            this.umurRealtimeText = `${Math.max(0, years)} thn ${Math.max(0, months)} bln`;
        },

        recalculateRealtimeTenorMax() {
            if (!this.form.produk || !this.form.jenis_pensiun || !this.form.tanggal_lahir || !this.form.tanggal_simulasi) {
                this.tenorMaxText = '-';
                this.realtimeTenorMaxValue = 0;
                return;
            }

            const productKey = `${this.form.produk}-${this.form.jenis_pensiun}`;
            const struct = this.productStructs[productKey] || null;
            if (!struct || !struct.tenor_max || !struct.usia_max) {
                this.tenorMaxText = '-';
                this.realtimeTenorMaxValue = 0;
                return;
            }

            const birth = new Date(this.form.tanggal_lahir + 'T00:00:00');
            const referenceDate = new Date(this.form.tanggal_simulasi + 'T00:00:00');
            if (Number.isNaN(birth.getTime()) || Number.isNaN(referenceDate.getTime())) return;

            let ageInMonths = (referenceDate.getFullYear() - birth.getFullYear()) * 12;
            ageInMonths += referenceDate.getMonth() - birth.getMonth();
            if (referenceDate.getDate() < birth.getDate()) ageInMonths -= 1;

            const usiaMaxInMonths = Number(struct.usia_max) * 12;
            const sisaMasa = Math.max(0, usiaMaxInMonths - ageInMonths);
            const tenorMax = Math.max(0, Math.min(sisaMasa, Number(struct.tenor_max)));
            this.realtimeTenorMaxValue = tenorMax;
            this.tenorMaxText = tenorMax > 0 ? `${Math.round(tenorMax)} bulan` : '0 bulan';
        },

        recalculateRealtimePlafondMax() {
            const productKey = `${this.form.produk}-${this.form.jenis_pensiun}`;
            const struct = this.productStructs[productKey] || null;
            if (!struct) {
                this.plafondMaxText = '-';
                return;
            }

            const tenorInput = this.form.tenor === '' || this.form.tenor === null
                ? this.realtimeTenorMaxValue
                : Number(this.form.tenor);

            if (!Number.isFinite(tenorInput) || tenorInput <= 0) {
                this.plafondMaxText = '-';
                return;
            }

            const sisaGaji = this.getRealtimeSisaGajiSaatPengajuan();
            if (!Number.isFinite(sisaGaji) || sisaGaji <= 0) {
                this.plafondMaxText = '-';
                return;
            }

            const rateToUse = (this.form.rate_percent_override && this.form.rate_percent_override !== '')
              ? Number(this.form.rate_percent_override)
              : (struct.rate_percent ? struct.rate_percent * 100 : 0);
            //console.log("rateTouse:",rateToUse, this.form.rate_percent_override, struct.rate_percent);  
            const rateBulanan = (rateToUse / 100) / 12;
            if (rateBulanan <= 0) return;
            const adm_ang = this.form.admin_angsuran_percent_override;
            const pmt = (sisaGaji-125000)/(1+adm_ang/100);
            const n = tenorInput;
            const pv = pmt * ((1 - Math.pow(1 + rateBulanan, -n)) / rateBulanan);
            //console.log("pmt:", pmt, "rateBulanan:", rateBulanan, "n:", n, "pv:", pv);
            //console.log("tenorInput:", tenorInput);
            //console.log('Recalculate Plafond Max:', { sisaGaji, rateBulanan, tenorInput, pv });     
            this.plafondMaxText = Number.isFinite(pv) && pv > 0 ? Math.round(pv) : '-';
        },

        getRowOptions(row) {
            if (!row || !row.optionsKey) return [];
            if (row.optionsKey === 'mutasi') return this.mutasiOptions;
            if (row.optionsKey === 'blokir') return this.blokirOptions;
            if (row.optionsKey === 'instansi') return this.instansiOptions;
            return Array.isArray(this.options[row.optionsKey]) ? this.options[row.optionsKey] : [];
        },
        getRenderableRows() { return this.excelRows; },
        isInputDisabled() { return false; },
        getFieldError() { return ''; },
        shouldShowMessages() { return true; },
        getRowDisplayValue(row) {
            if (row.key === 'umur_text') return this.umurRealtimeText;
            if (row.key === 'tenor_max') return this.tenorMaxText;
            if (row.key === 'plafond_max') {
                if (typeof this.plafondMaxText === 'number') {
                    return 'Rp ' + Math.round(this.plafondMaxText).toLocaleString('id-ID');
                }
                return this.plafondMaxText;
            }
            if (row.key === 'sisa_gaji_saat_pengajuan') {
                return 'Rp ' + Math.round(this.getRealtimeSisaGajiSaatPengajuan()).toLocaleString('id-ID');
            }
            if (row.staticValue) return row.staticValue;
            if (this.hasil && this.hasil[row.key] !== undefined) {
                const val = this.hasil[row.key];
                if (row.format === 'currency' && typeof val === 'number') {
                    return 'Rp ' + Math.round(val).toLocaleString('id-ID');
                }
                return val;
            }
            return '-';
        }
    };
}
</script>