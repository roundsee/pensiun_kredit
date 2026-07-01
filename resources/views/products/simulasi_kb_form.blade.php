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
    .kb-sheet-input:disabled {
        background: #eef2f6;
        border-color: #d8dee4;
    }
    .kb-trial-note {
        background: #e9f5ff;
        border-color: #98c9ea;
    }
    .kb-trial-note:focus {
        background: #f4fbff;
        border-color: #63abd9;
        box-shadow: 0 0 0 0.2rem rgba(99, 171, 217, 0.18);
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

                    <div class="d-flex justify-content-end gap-2 mb-3">
                        <a href="{{ route('kb_simulasi.goal_seeker') }}" class="btn btn-sm btn-outline-primary">Goal Seeker</a>
                        <a href="{{ route('data_simulasi.trial.list') }}" class="btn btn-sm btn-outline-secondary">Lihat Trial Data Simulasi</a>
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

                    <div class="alert alert-warning mt-3 mb-0" x-show="shouldShowMessages() && limitWarning" x-text="limitWarning"></div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="button" 
                                @click="simpan()" 
                                :disabled="!isValidForm || isSaving" 
                                :class="!isValidForm ? 'opacity-50 cursor-not-allowed' : ''"
                                class="btn btn-primary">
                            <span x-text="isSaving ? 'Menyimpan...' : 'Simpan'"></span>
                        </button>
                        <button class="btn btn-outline-danger" type="button" @click="downloadPdf" :disabled="!hasil || isDownloading" x-text="isDownloading ? 'Menyiapkan PDF...' : 'Download PDF'"></button>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Keterangan Trial</label>
                        <textarea class="form-control kb-trial-note" rows="3" x-model="form.keterangan" placeholder="Contoh: debitur minta sisa gaji akhir nya minimal 250rb"></textarea>
                        <div class="form-text">Keterangan ini akan tampil di list Trial Data Simulasi.</div>
                    </div>
                    <div class="small text-muted mt-2" x-show="isCalculating">Menghitung otomatis...</div>

                </div>
            </div>
        </div>
    </div>
</div>

@php
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
<script type="application/json" id="kb-initial-data">{!! json_encode($kbInitialData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/json" id="kb-permissions">{!! json_encode($kbPermissions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/json" id="kb-simulasi-routes">{!! json_encode($kbSimulasiRoutes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
<script>
function kbSimulasiForm() {
    const options = JSON.parse(document.getElementById('kb-simulasi-options')?.textContent || '{}');
    const productStructs = JSON.parse(document.getElementById('kb-product-structs')?.textContent || '{}');
    const initialData = JSON.parse(document.getElementById('kb-initial-data')?.textContent || 'null');
    const permissions = JSON.parse(document.getElementById('kb-permissions')?.textContent || '{}');
    const routes = JSON.parse(document.getElementById('kb-simulasi-routes')?.textContent || '{}');
    const csrf = '{{ csrf_token() }}';
    let goalSeekerTransfer = null;
    try {
        goalSeekerTransfer = JSON.parse(sessionStorage.getItem('kb_goal_seeker_transfer') || 'null');
    } catch (error) {
        goalSeekerTransfer = null;
    }
    
    return {
        options,
        productStructs,
        permissions,
        editDataSimulasiId: initialData && initialData.id ? Number(initialData.id) : null,
        form: {
            produk: '',
            jenis_pensiun: 'Sendiri',
            mutasi: 'Non Mutasi',
            bank_asal: '',
            bank_tujuan: '',
            keterangan: '',
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
        blokirOptions: ['1', '2', '3', '4', '5'],
        instansiOptions: ['TASPEN', 'ASABRI'],
        excelRows: [
            { cell: 'E10', label: 'Produk', type: 'select', key: 'produk', optionsKey: 'produk', allowEmpty: true },
            { cell: 'E11', label: 'Jenis Pensiun', type: 'select', key: 'jenis_pensiun', optionsKey: 'jenis_pensiun' },
            { cell: 'E12', label: 'Mutasi', type: 'select', key: 'mutasi', optionsKey: 'mutasi' },
            //{ cell: 'E13', label: 'Bank Asal', type: 'output', key: 'bank_asal', format: 'text', staticValue: 'BANK BUKOPIN' },
            { cell: 'E13', label: 'Bank Asal', type: 'select', key: 'bank_asal', optionsKey: 'bank_asal', allowEmpty: true },
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
            { cell: 'E24', label: 'Angsuran Lainnya', type: 'integer', key: 'angsuran_lainnya' },
            { cell: 'E25', label: 'Sisa Gaji saat pengajuan', type: 'output', key: 'sisa_gaji_saat_pengajuan', format: 'currency' },
            { cell: 'E26', label: 'Tenor Max', type: 'output', key: 'tenor_max', format: 'months' },
            { cell: 'E26A', label: 'Rate (%) Override', type: 'number', key: 'rate_percent_override', onlyRoleCanEditPricing: true },
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
        isValidForm: true,
        umurRealtimeText: '-',
        tenorMaxText: '-',
        plafondMaxText: '-',
        limitWarning:'',
        realtimeTenorMaxValue: 0,
        calcRequestSeq: 0,
        calcInFlightSignature: '',
        calcLastCompletedSignature: '',
        calcAbortController: null,
        enableAutoServerCalc: true, // Diubah menjadi true agar auto-calculate aktif
        isCalculating: false,
        isSaving: false,
        isDownloading: false,
        autoCalculateTimer: null,
        plafondTimer: null, // Timer khusus untuk plafond
        message: '',
        errorMessage: '',
        limitWarning: '',
        fieldMessages: {},

        init() {
            this.applyInitialData();
            this.applyGoalSeekerTransferData();
            this.applyInitialDefaults();
            this.syncAllSelectValues();

            const watchedPaths = [
                'form.produk', 'form.jenis_pensiun', 'form.mutasi', 'form.bank_tujuan','form.bank_asal',
                'form.nama_debitur', 'form.tanggal_simulasi', 'form.tanggal_lahir',
                'form.nomor_pensiun', 'form.instansi', 'form.gaji_pensiun',
                'form.angsuran_lainnya', 'form.blokir_angsuran', 'form.rate_percent_override',
                'form.admin_angsuran_percent_override', 'form.tenor', 'form.plafond',
                'form.pelunasan', 'form.nama_marketing', 'form.kode_area',
                'form.keterangan',
            ];

            watchedPaths.forEach((path) => {
                this.$watch(path, () => { this.onFormChanged(); });
            });

            // Watch khusus untuk plafond dengan debounce
            this.$watch('form.plafond', (newValue, oldValue) => {
                if (newValue && newValue > 0 && this.form.tenor && this.form.produk) {
                    // Debounce untuk menghindari terlalu banyak request
                    if (this.plafondTimer) clearTimeout(this.plafondTimer);
                    this.plafondTimer = setTimeout(() => {
                        this.hitung(true);
                    }, 500);
                }
            });

            // Watch untuk tenor dan produk juga memicu kalkulasi
            this.$watch('form.tenor', () => {
                if (this.form.plafond && this.form.plafond > 0 && this.form.produk) {
                    if (this.plafondTimer) clearTimeout(this.plafondTimer);
                    this.plafondTimer = setTimeout(() => {
                        this.hitung(true);
                    }, 500);
                }
            });

            this.$watch('form.produk', () => { 
                this.updateRateDefaults(); 
                if (this.form.plafond && this.form.plafond > 0 && this.form.tenor) {
                    if (this.plafondTimer) clearTimeout(this.plafondTimer);
                    this.plafondTimer = setTimeout(() => {
                        this.hitung(true);
                    }, 500);
                }
            });
            this.$watch('form.jenis_pensiun', () => { 
                this.updateRateDefaults();
                if (this.form.plafond && this.form.plafond > 0 && this.form.tenor && this.form.produk) {
                    if (this.plafondTimer) clearTimeout(this.plafondTimer);
                    this.plafondTimer = setTimeout(() => {
                        this.hitung(true);
                    }, 500);
                }
            });

            this.$nextTick(() => {
                this.applyInitialData();
                this.applyInitialDefaults();
                this.updateRateDefaults();
                this.syncAllSelectValues();
                this.onFormChanged();

                setTimeout(() => {
                    this.updateRateDefaults();
                    this.syncAllSelectValues();
                    // Jika ada plafond awal, langsung hitung
                    if (this.form.plafond && this.form.plafond > 0 && this.form.tenor && this.form.produk) {
                        this.hitung(true);
                    }
                }, 100);
            });
        },

        applyInitialData() {
            console.log('Applying initial data:', initialData);
            if (!initialData || !initialData.id) return;

            this.hasil = { ...(this.hasil || {}), ...initialData };

            Object.keys(this.form).forEach((key) => {
                if (Object.prototype.hasOwnProperty.call(initialData, key) && initialData[key] !== null && initialData[key] !== undefined) {
                    const integerFields = ['gaji_pensiun', 'angsuran_lainnya', 'plafond', 'pelunasan', 'tenor', 'rate_percent_override', 'admin_angsuran_percent_override'];
                    if (integerFields.includes(key)) {
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

        applyGoalSeekerTransferData() {
            if (!goalSeekerTransfer || this.editDataSimulasiId) {
                return;
            }

            Object.keys(this.form).forEach((key) => {
                if (!Object.prototype.hasOwnProperty.call(goalSeekerTransfer, key)) {
                    return;
                }

                const value = goalSeekerTransfer[key];
                if (value === null || value === undefined || value === '') {
                    return;
                }

                if (key === 'blokir_angsuran') {
                    this.form[key] = String(value);
                    return;
                }

                this.form[key] = value;
            });

            this.message = 'Data dari Goal Seeker sudah dimuat. Silakan review lalu Simpan.';

            try {
                sessionStorage.removeItem('kb_goal_seeker_transfer');
            } catch (error) {
                // no-op
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

            const produkOptions = Array.isArray(this.options.produk) ? this.options.produk : [];
            const platinumOption = produkOptions.find((item) => String(item).trim().toLowerCase() === 'platinum');
            const produkDefault = platinumOption || produkOptions[0] || 'Platinum';

            if (!this.form.jenis_pensiun || !jenisOptions.includes(this.form.jenis_pensiun)) {
                this.form.jenis_pensiun = jenisDefault;
            }

            if (!this.form.produk || String(this.form.produk).trim() === '') {
                this.form.produk = produkDefault;
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

            this.applyAutoProdukByAge();
            
            const productKey = `${this.form.bank_tujuan}-${this.form.produk}-${this.form.jenis_pensiun}`;
           console.log('Applying initial defaults for productKey:', productKey);
            if (this.form.produk && this.productStructs[productKey]) {
                const struct = this.productStructs[productKey];
                console.log('Found product struct:', struct);
                
                if ((this.form.rate_percent_override === '' || this.form.rate_percent_override === null) && struct.rate_percent !== undefined) {
                    this.form.rate_percent_override = Math.round(Number(struct.rate_percent * 100));
                }
                
                if ((this.form.admin_angsuran_percent_override === '' || this.form.admin_angsuran_percent_override === null) && struct.admin_angsuran_percent !== undefined) {
                    this.form.admin_angsuran_percent_override = Math.round(Number(struct.admin_angsuran_percent * 100));
                }
            }
        },

        updateRateDefaults() {
            const productKey = `${this.form.bank_tujuan}-${this.form.produk}-${this.form.jenis_pensiun}`;
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

        getCurrentAgeYears() {
            if (!this.form.tanggal_lahir) return null;

            const birth = new Date(this.form.tanggal_lahir + 'T00:00:00');
            const referenceDate = this.form.tanggal_simulasi
                ? new Date(this.form.tanggal_simulasi + 'T00:00:00')
                : new Date();

            if (Number.isNaN(birth.getTime()) || Number.isNaN(referenceDate.getTime())) {
                return null;
            }

            let years = referenceDate.getFullYear() - birth.getFullYear();
            let months = referenceDate.getMonth() - birth.getMonth();

            if (referenceDate.getDate() < birth.getDate()) {
                months -= 1;
            }
            if (months < 0) {
                years -= 1;
            }

            return Math.max(0, years);
        },

        applyAutoProdukByAge() {
            const produkOptions = Array.isArray(this.options.produk) ? this.options.produk : [];
            if (produkOptions.length === 0) return;

            const regularOption = produkOptions.find((item) => String(item).trim().toLowerCase() === 'regular') || 'Regular';
            const platinumOption = produkOptions.find((item) => String(item).trim().toLowerCase() === 'platinum') || 'Platinum';

            const ageYears = this.getCurrentAgeYears();
            const nextProduk = ageYears !== null && ageYears < 68 ? regularOption : platinumOption;

            if (this.form.produk !== nextProduk) {
                this.form.produk = nextProduk;
            }
        },

        onFormChanged() {
            console.log('Form changed:', this.form);
            this.applyInitialDefaults();
            this.applyAutoProdukByAge();
            this.syncAllSelectValues();
            this.recalculateRealtimeAge();
            this.recalculateRealtimeTenorMax();
            this.recalculateRealtimePlafondMax();
            this.recalculateClientSide();
            this.scheduleAutoCalculate();
        },

        recalculateClientSide() {
            // Fungsi untuk kalkulasi client-side jika diperlukan
            // Saat ini hanya trigger auto calculate ke server
            if (this.enableAutoServerCalc && this.form.plafond && this.form.plafond > 0 && this.form.tenor && this.form.produk) {
                this.scheduleAutoCalculate();
            }
        },

        scheduleAutoCalculate() {
            if (!this.enableAutoServerCalc) return;
            
            // Samakan dengan GoalSeeker: cukup data inti terisi untuk hitung standar backend,
            // agar plafond max berasal dari server (bukan hanya fallback realtime frontend).
            if (!this.form.produk || !this.form.jenis_pensiun || !this.form.bank_tujuan || !this.form.tanggal_simulasi || !this.form.tanggal_lahir) {
                return;
            }

            if (this.autoCalculateTimer) {
                clearTimeout(this.autoCalculateTimer);
            }
            
            this.autoCalculateTimer = setTimeout(() => {
                this.hitung(true);
            }, 800);
        },

        buildLimitFeedback(limits) {
            const warnings = [];
            const fieldMessages = {};

            if (!limits) {
                return { warning: '', fieldMessages };
            }

            if (limits.usia_min_valid === false) {
                warnings.push(`Usia masuk di bawah batas minimum ${limits.usia_masuk_min} tahun`);
                fieldMessages.tanggal_lahir = `Usia masuk minimal ${limits.usia_masuk_min} tahun`;
            }

            if (limits.usia_max_valid === false) {
                warnings.push(`Usia melebihi batas maksimum ${limits.usia_lunas_max} tahun`);
                fieldMessages.tanggal_lahir = `Usia maksimum ${limits.usia_lunas_max} tahun`;
            }

            if (limits.tenor_valid === false) {
                warnings.push(`Tenor input melebihi tenor max ${limits.tenor_max} bulan`);
                fieldMessages.tenor = `Maksimal ${limits.tenor_max} bulan`;
            }

            if (limits.plafond_valid === false) {
                warnings.push(`Plafond input melebihi plafond max`);
                fieldMessages.plafond = 'Plafond melebihi batas maksimal';
            }

            if (limits.total_angsuran_valid === false) {
                warnings.push(`Total angsuran melebihi batas kemampuan bayar`);
                fieldMessages.tenor = fieldMessages.tenor || 'Total angsuran terlalu besar';
                fieldMessages.plafond = fieldMessages.plafond || 'Turunkan plafond atau tenor';
            }

            if (limits.sisa_gaji_akhir_valid === false) {
                warnings.push(`Sisa gaji akhir di bawah minimum Rp ${Math.round(Number(limits.sisa_gaji_akhir_min || 0)).toLocaleString('id-ID')}`);
            }

            if (limits.terima_bersih_valid === false) {
                warnings.push('Terima bersih harus lebih besar dari 0');
            }

            return {
                warning: warnings.length > 0 ? `Data belum valid: ${warnings.join('; ')}` : '',
                fieldMessages,
            };
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

        syncSelectValue(row) {
    if (!row || row.type !== 'select' || !row.key) return;
   
    const optionsList = this.getRowOptions(row) || [];
    if (optionsList.length === 0) return;

    const current = this.form[row.key];
    const currentNormalized = this.normalizeOptionValue(current);
    const matched = optionsList.find((item) => this.normalizeOptionValue(item) === currentNormalized) ?? null;

    if (matched !== null) {
        this.form[row.key] = matched;
        return;
    }
    
    // PERBAIKAN: Jangan paksa reset atau berikan nilai default jika user memilih opsi kosong/tertentu pada bank
    if (row.key === 'produk' || row.key === 'bank_tujuan' || row.key === 'bank_asal') {
        return;
    }

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
        let days = referenceDate.getDate() - birth.getDate();

        // Jika hari minus, artinya belum melewati tanggal lahir di bulan berjalan
        if (days < 0) {
            months -= 1;
            // Cari jumlah hari pada bulan sebelumnya untuk tahu sisa hari riil
            let prevMonth = new Date(referenceDate.getFullYear(), referenceDate.getMonth(), 0).getDate();
            days = prevMonth + days;
        }

        if (months < 0) {
            years -= 1;
            months += 12;
        }

        // LOGIKA ANDA: Jika ada kelebihan hari (> 0), bulatkan ke atas menjadi +1 bulan
        if (days > 0) {
            months += 1;
        }

        // Jika setelah ditambah bulan menjadi 12, konversi ke tahun
        if (months >= 12) {
            years += 1;
            months = 0;
        }

        this.umurRealtimeText = `${Math.max(0, years)} thn ${Math.max(0, months)} bln`;
            // let years = referenceDate.getFullYear() - birth.getFullYear();
            // let months = referenceDate.getMonth() - birth.getMonth();
            // if (referenceDate.getDate() < birth.getDate()) months -= 1;
            // if (months < 0) {
            //     years -= 1;
            //     months += 12;
            // }
            // this.umurRealtimeText = `${Math.max(0, years)} thn ${Math.max(0, months)} bln`;
        },

        recalculateRealtimeTenorMax() {
console.log('=== recalculateRealtimeTenorMax() Dipicu ===', {
        produk: this.form.produk,
        jenis_pensiun: this.form.jenis_pensiun,
        tanggal_lahir: this.form.tanggal_lahir,
        tanggal_simulasi: this.form.tanggal_simulasi,
        bank_tujuan: this.form.bank_tujuan
    });            
            if (!this.form.produk || !this.form.jenis_pensiun || !this.form.tanggal_lahir || !this.form.tanggal_simulasi) {
                this.tenorMaxText = '-';
                this.realtimeTenorMaxValue = 0;
                return;
            }

            const productKey = `${this.form.bank_tujuan}-${this.form.produk}-${this.form.jenis_pensiun}`;
            const struct = this.productStructs[productKey] || null;
            console.log('Recalculating tenor max for productKey:', productKey, 'with struct:', struct);
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
            const productKey = `${this.form.bank_tujuan}-${this.form.produk}-${this.form.jenis_pensiun}`;
            console.log('Recalculating plafond max for productKey:', productKey);
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
                : (struct.rate_percent ? Number(struct.rate_percent) * 100 : 0);

            const adminAngsuranToUse = (this.form.admin_angsuran_percent_override && this.form.admin_angsuran_percent_override !== '')
                ? Number(this.form.admin_angsuran_percent_override)
                : (struct.admin_angsuran_percent ? Number(struct.admin_angsuran_percent) * 100 : 0);

            const dbrToUse = struct.dbr_percent ? Number(struct.dbr_percent) : 0;

            const rateBulanan = (rateToUse / 100) / 12;
            const adminAngsuran = adminAngsuranToUse / 100;
            const ratioGajiMax = dbrToUse > 1 ? dbrToUse / 100 : dbrToUse;

            if (rateBulanan <= 0 || ratioGajiMax <= 0) {
                this.plafondMaxText = '-';
                return;
            }

            // Samakan dengan Excel/backend:
            // basis = MIN(E25*C20, (E25-120000)/(1+D35))
            const kandidatPertama = sisaGaji * ratioGajiMax;
            const kandidatKedua = (sisaGaji - 120000) / (1 + adminAngsuran);
            const basisAngsuran = Math.min(kandidatPertama, kandidatKedua);
            const basisAngsuranPokok = basisAngsuran - 10000;

            if (!Number.isFinite(basisAngsuranPokok) || basisAngsuranPokok <= 0) {
                this.plafondMaxText = '-';
                return;
            }

            const n = tenorInput;
            const pv = basisAngsuranPokok * ((1 - Math.pow(1 + rateBulanan, -n)) / rateBulanan);
            this.plafondMaxText = Number.isFinite(pv) && pv > 0 ? Math.round(pv) : '-';
        },

        async hitung(silent = false) {
            // Validasi input wajib murni
            if (!this.form.produk || !this.form.tenor || !this.form.plafond) {
                if (silent) {
                    // Saat auto-calc, izinkan call tanpa tenor/plafond agar backend tetap bisa mengembalikan tenor_max/plafond_max.
                } else {
                    this.errorMessage = 'Produk, Tenor, dan Plafond harus diisi untuk menghitung';
                    setTimeout(() => { this.errorMessage = ''; }, 3000);
                    return;
                }
            }
            
            // HAPUS ATAU KOMENTARI BLOKADE VALIDASI PLAFOND MAX DI SINI
            // Biarkan backend Laravel yang menguji kevalidan angkanya agar data tetap mengalir
            
            // Abort request sebelumnya jika ada
            if (this.calcAbortController) {
                this.calcAbortController.abort();
            }
            
            this.calcAbortController = new AbortController();
            this.isCalculating = true;
            
            if (!silent) {
                this.message = 'Menghitung...';
            }
            this.errorMessage = '';
            this.limitWarning = '';
            this.fieldMessages = {};
            
            try {
                const response = await fetch(routes.calculate, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        produk: this.form.produk,
                        jenis_pensiun: this.form.jenis_pensiun,
                        bank_tujuan: this.form.bank_tujuan,
                        mutasi: this.form.mutasi,
                        plafond: this.form.plafond,
                        tenor: this.form.tenor,
                        rate_percent_override: this.form.rate_percent_override,
                        admin_angsuran_percent_override: this.form.admin_angsuran_percent_override,
                        tanggal_lahir: this.form.tanggal_lahir,
                        tanggal_simulasi: this.form.tanggal_simulasi,
                        instansi: this.form.instansi,
                        gaji_pensiun: this.form.gaji_pensiun,
                        angsuran_lainnya: this.form.angsuran_lainnya,
                        blokir_angsuran: this.form.blokir_angsuran,
                        pelunasan: this.form.pelunasan
                    }),
                    signal: this.calcAbortController.signal
                });
// === AMANKAN CHECK CONTENT TYPE DI SINI ===
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const textError = await response.text();
                    console.error('SERVER ERROR (HTML/TEXT):', textError); // Lihat error detail di F12 Console
                    
                    this.isValidForm = false;
                    this.errorMessage = 'Server Error saat menghitung. Silakan cek tab Console F12 / Network.';
                    this.isCalculating = false;
                    return; 
                }                
                const data = await response.json();

// Reset semua notifikasi setiap kali hitung dimulai jika format JSON valid
                this.message = '';
                this.errorMessage = '';
                this.limitWarning = '';
                this.fieldMessages = {};
                
                // PERBAIKAN UTAMA: Selama server mengirimkan array data kalkulasi, 
                // langsung kunci dan tampilkan ke layar agar Provisi/Admin tidak jadi (-)
                if (data.data) {
                    this.hasil = data.data;
                    this.hasilDisplay = Object.entries(data.data).map(([key, value]) => ({ key, value }));
                }
                if (data.limits) {
                    this.isValidForm = data.limits.is_valid; 
                } else {
                    this.isValidForm = true; 
                }

                const limitFeedback = this.buildLimitFeedback(data.limits);
                this.fieldMessages = limitFeedback.fieldMessages;

                // Urus notifikasi sukses/gagal secara terpisah tanpa merusak tampilan tabel
                if (data.success || (data.limits && data.limits.is_valid)) {
                    if (!silent) {
                        this.message = 'Perhitungan berhasil';
                        setTimeout(() => { this.message = ''; }, 3000);
                    }
                } else {
                    this.limitWarning = limitFeedback.warning || 'Perhitungan berhasil, tetapi ada data yang belum valid.';
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Error:', error);
                    if (!silent) {
                        this.errorMessage = 'Terjadi kesalahan saat menghitung';
                        setTimeout(() => { this.errorMessage = ''; }, 3000);
                    }
                }
            } finally {
                this.isCalculating = false;
                this.calcAbortController = null;
            }
        },

        async simpan() {
    if (!this.hasil) {
        this.errorMessage = 'Tidak ada data hasil perhitungan untuk disimpan';
        setTimeout(() => { this.errorMessage = ''; }, 3000);
        return;
    }
    
    this.isSaving = true;
    this.message = 'Menyimpan data...';
    this.errorMessage = ''; 
    
    try {
        // PERBAIKAN PAYLOAD: Pastikan bank_tujuan dan bank_asal dipastikan ikut masuk
        const payload = {
            ...this.form,
            ...this.hasil,
            bank_tujuan: this.form.bank_tujuan,
            bank_asal: this.form.bank_asal,
            instansi: this.form.instansi,
            nama_debitur: this.form.nama_debitur,   // <--- Tambahkan ini
            nomor_pensiun: this.form.nomor_pensiun, // <--- Tambahkan ini
            id: this.editDataSimulasiId || null
        };
        
        console.log('Payload dikirim ke store:', payload); // Log untuk debug

        const response = await fetch(routes.store, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        });
        
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            const textError = await response.text();
            console.error('SERVER ERROR (HTML/TEXT):', textError);
            this.errorMessage = 'Server Error (500/422). Silakan cek tab Console F12 / Network.';
            this.isSaving = false; // RESET STATUS AGAR TOMBOL KEMBALI NORMAL
            return; 
        }
        
        const data = await response.json();
        console.log('Simpan response SUKSES JSON:', data);
        
        if (response.ok) { 
            this.message = data.message || 'Data berhasil disimpan';
            setTimeout(() => { this.message = ''; }, 3000);
            
            if (data.id && !this.editDataSimulasiId) {
                this.editDataSimulasiId = data.id;
            }
            // SINKRONKAN ID KE DATA HASIL AGAR UNDUH PDF BISA JALAN
            this.hasil.id = data.id;
        } else {
            this.errorMessage = data.message || 'Gagal menyimpan data';
            setTimeout(() => { this.errorMessage = ''; }, 5000);
        }
    } catch (error) {
        console.error('Error Catch Terjadi:', error);
        this.errorMessage = 'Terjadi kesalahan saat menyimpan data';
        setTimeout(() => { this.errorMessage = ''; }, 3000);
    } finally {
        this.isSaving = false; // PASTIKAN SELALU RESET DI SINI
    }
},

        async downloadPdf() {
    // 1. Pastikan simulasi sudah dihitung dan data ID-nya sudah ada (setelah disimpan ke DB)
    // Sesuai dengan data backend yang membutuhkan ID data simulasi.
    if (!this.hasil || !this.hasil.id) {
        this.errorMessage = 'Simpan atau hitung simulasi terlebih dahulu sebelum mendownload PDF';
        return;
    }

    this.isDownloading = true;
    this.message = 'Sedang menyiapkan PDF...';
    this.errorMessage = ''; // Reset error message jika ada sebelumnya

    try {
        // 2. Cukup kirim payload berisi ID saja
        const payload = {
            id: this.hasil.id
        };

        const response = await fetch(routes.downloadPdf, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            throw new Error('Gagal men-generate PDF dari server');
        }

        // Ambil respon sebagai BLOB (Binary Large Object)
        const blob = await response.blob();
        
        // Buat tautan unduhan buatan di memori browser
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        
        // Atur nama file default download (bisa pakai nama debitur dari form jika di DB belum sinkron saat itu)
        const timestamp = new Date().toISOString().slice(0,10).replace(/-/g, '');
        a.download = `simulasi-kb-${this.form.nama_debitur || 'debitur'}-${timestamp}.pdf`;
        
        document.body.appendChild(a);
        a.click();
        
        // Bersihkan memori browser setelah download terpicu
        window.URL.revokeObjectURL(url);
        a.remove();

        this.message = 'PDF berhasil didownload';
        setTimeout(() => { this.message = ''; }, 3000);

    } catch (error) {
        console.error('Error Download PDF:', error);
        this.errorMessage = 'Terjadi kesalahan saat mengunduh PDF';
    } finally {
        this.isDownloading = false;
    }
},

        getRowOptions(row) {
            if (!row || !row.optionsKey) return [];
            if (row.optionsKey === 'mutasi') return this.mutasiOptions;
            if (row.optionsKey === 'blokir') return this.blokirOptions;
            if (row.optionsKey === 'instansi') return this.instansiOptions;
            return Array.isArray(this.options[row.optionsKey]) ? this.options[row.optionsKey] : [];
        },
        
        getRenderableRows() { 
            return this.excelRows; 
        },
        
        isInputDisabled(row) { 
            if (row && row.key === 'produk') {
                return true;
            }
            if (row.onlyRoleCanEditPricing && !this.permissions.can_edit_pricing) {
                return true;
            }
            return false; 
        },
        
        getFieldError(key) { 
            return this.fieldMessages[key] || ''; 
        },
        
        shouldShowMessages() { 
            return true; 
        },
        
        getRowDisplayValue(row) {
            if (row.key === 'umur_text') {
                if (this.umurRealtimeText && this.umurRealtimeText !== '-') {
                    return this.umurRealtimeText;
                }
                if (this.hasil && this.hasil.umur_text) {
                    return this.hasil.umur_text;
                }
                return '-';
            }
            if (row.key === 'tenor_max') {
                if (this.tenorMaxText && this.tenorMaxText !== '-') {
                    return this.tenorMaxText;
                }
                if (this.hasil && this.hasil.tenor_max !== undefined && this.hasil.tenor_max !== null && this.hasil.tenor_max !== '') {
                    return `${Math.round(Number(this.hasil.tenor_max) || 0)} bulan`;
                }
                return '-';
            }
            if (row.key === 'plafond_max') {
                if (this.hasil && this.hasil.plafond_max !== undefined && this.hasil.plafond_max !== null && this.hasil.plafond_max !== '') {
                    return 'Rp ' + Math.round(Number(this.hasil.plafond_max) || 0).toLocaleString('id-ID');
                }
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
                if (row.format === 'date' && val) {
                    return val;
                }
                return val;
            }
            return '-';
        }
    };
}
</script>
@endsection