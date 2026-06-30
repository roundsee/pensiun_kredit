@extends('layouts.app')

@section('content')
<div class="container" x-data="kbGoalSeeker()">
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="card mb-3">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span>Goal Seeker Simulasi KB</span>
                    <a href="{{ route('kb_simulasi.index') }}" class="btn btn-sm btn-outline-secondary">Kembali ke Simulasi</a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" x-show="message" x-text="message"></div>
                    <div class="alert alert-danger" x-show="errorMessage" x-text="errorMessage"></div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Produk</label>
                            <select class="form-select" x-model="form.produk">
                                <template x-for="item in options.produk" :key="item">
                                    <option :value="item" x-text="item"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Pensiun</label>
                            <select class="form-select" x-model="form.jenis_pensiun">
                                <template x-for="item in options.jenis_pensiun" :key="item">
                                    <option :value="item" x-text="item"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bank Tujuan</label>
                            <select class="form-select" x-model="form.bank_tujuan">
                                <template x-for="item in options.bank_tujuan" :key="item">
                                    <option :value="item" x-text="item"></option>
                                </template>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Simulasi</label>
                            <input type="date" class="form-control" x-model="form.tanggal_simulasi">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" x-model="form.tanggal_lahir">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Instansi</label>
                            <select class="form-select" x-model="form.instansi">
                                <option value="TASPEN">TASPEN</option>
                                <option value="ASABRI">ASABRI</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Gaji Pensiun</label>
                            <input type="number" class="form-control" x-model.number="form.gaji_pensiun">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Angsuran Lainnya</label>
                            <input type="number" class="form-control" x-model.number="form.angsuran_lainnya">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Blokir Angsuran (Bulan)</label>
                            <select class="form-select" x-model="form.blokir_angsuran">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Pelunasan</label>
                            <input type="number" class="form-control" x-model.number="form.pelunasan">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Adm Angsuran (%) Override</label>
                            <input type="number" step="any" class="form-control" x-model.number="form.admin_angsuran_percent_override" :disabled="!permissions.can_edit_pricing">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Rate (%) Override</label>
                            <input type="number" step="any" class="form-control" x-model.number="form.rate_percent_override" :disabled="!permissions.can_edit_pricing">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tenor Min</label>
                            <input type="number" class="form-control" x-model.number="form.tenor_min" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tenor Max (opsional)</label>
                            <input type="number" class="form-control" x-model.number="form.tenor_max" min="1">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allow-rate-search" x-model="form.allow_rate_search" :disabled="!permissions.can_edit_pricing">
                                <label class="form-check-label" for="allow-rate-search">Izinkan naik/turun rate</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Target Angsuran Max</label>
                            <input type="number" class="form-control" placeholder="Contoh: 1250000" x-model.number="form.target_angsuran_max">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Target Sisa Gaji Akhir Min</label>
                            <input type="number" class="form-control" placeholder="Contoh: 250000" x-model.number="form.target_sisa_gaji_akhir_min">
                        </div>
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
                            <div class="fw-semibold" x-text="hasil.rate_percent_override + ' %'"></div>
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
        message: '',
        errorMessage: '',
        checkedCount: 0,
        hasil: null,
        form: {
            produk: (options.produk && options.produk.length > 0) ? options.produk[0] : 'Platinum',
            jenis_pensiun: 'Sendiri',
            mutasi: 'Non Mutasi',
            bank_asal: '',
            bank_tujuan: (options.bank_tujuan && options.bank_tujuan.length > 0) ? options.bank_tujuan[0] : 'KB',
            tanggal_simulasi: today,
            tanggal_lahir: '',
            instansi: 'TASPEN',
            gaji_pensiun: '',
            angsuran_lainnya: 0,
            blokir_angsuran: '1',
            pelunasan: 0,
            rate_percent_override: '',
            admin_angsuran_percent_override: '',
            tenor_min: 1,
            tenor_max: '',
            allow_rate_search: true,
            target_angsuran_max: '',
            target_sisa_gaji_akhir_min: '',
        },

        formatCurrency(value) {
            const amount = Number(value || 0);
            return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
        },

        async cari() {
            this.isSearching = true;
            this.message = '';
            this.errorMessage = '';
            this.hasil = null;

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
                this.hasil = {
                    ...data.data,
                    rate_percent_override: data.selected ? data.selected.rate_percent_override : null,
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
