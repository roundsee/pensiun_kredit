@extends('layouts.app')

@section('content')
@php
    $debtorInit    = $template->templateFields->where('section','informasi_debitur')->values();
    $submissionInit= $template->templateFields->where('section','data_pengajuan')->values();
    $financialInit = $template->templateFields->where('section','data_financial')->values();
@endphp

<div class="container" x-data="templateEditForm(
    {{ Js::from($debtorInit) }},
    {{ Js::from($submissionInit) }},
    {{ Js::from($financialInit) }}
)">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Template: {{ $template->template_name }}</h4>
        <a href="{{ route('product_templates.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('product_templates.update', $template) }}">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header fw-bold">Informasi Template</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Template <span class="text-danger">*</span></label>
                    <input type="text" name="template_name" class="form-control" value="{{ old('template_name', $template->template_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Deskripsi</label>
                    <input type="text" name="template_description" class="form-control" value="{{ old('template_description', $template->template_description) }}">
                </div>
            </div>
        </div>

        {{-- DATA DEBITUR --}}
        <div class="card mb-3">
            <div class="card-header fw-bold d-flex justify-content-between">
                Data Debitur
                <button type="button" class="btn btn-sm btn-outline-success" @click="addRow('informasi_debitur')">+ Tambah Baris</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Field Label</th><th>Field Name</th><th>Tipe</th><th>Wajib</th><th></th></tr></thead>
                    <tbody>
                        <template x-for="(row, i) in debtorRows" :key="row._id">
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" :name="`fields[${globalIndex(row)}][field_label]`" x-model="row.field_label" @input="row.field_name = toSnake(row.field_label)" required></td>
                                <td><input type="text" class="form-control form-control-sm" :name="`fields[${globalIndex(row)}][field_name]`" x-model="row.field_name"></td>
                                <td>
                                    <input type="hidden" :name="`fields[${globalIndex(row)}][section]`" value="informasi_debitur">
                                    <select class="form-select form-select-sm" :name="`fields[${globalIndex(row)}][field_type]`" x-model="row.field_type">
                                        <option value="text">Text</option><option value="number">Number</option><option value="date">Date</option><option value="dropdown">Dropdown</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="hidden" :name="`fields[${globalIndex(row)}][is_required]`" value="0">
                                    <input type="checkbox" :name="`fields[${globalIndex(row)}][is_required]`" value="1" x-model="row.is_required">
                                </td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" @click="removeRow('informasi_debitur', i)">Hapus</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- DATA PENGAJUAN --}}
        <div class="card mb-3">
            <div class="card-header fw-bold d-flex justify-content-between">
                Data Pengajuan
                <button type="button" class="btn btn-sm btn-outline-success" @click="addRow('data_pengajuan')">+ Tambah Baris</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Field Label</th><th>Field Name</th><th>Tipe</th><th>Wajib</th><th></th></tr></thead>
                    <tbody>
                        <template x-for="(row, i) in submissionRows" :key="row._id">
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" :name="`fields[${globalIndex(row)}][field_label]`" x-model="row.field_label" @input="row.field_name = toSnake(row.field_label)" required></td>
                                <td><input type="text" class="form-control form-control-sm" :name="`fields[${globalIndex(row)}][field_name]`" x-model="row.field_name"></td>
                                <td>
                                    <input type="hidden" :name="`fields[${globalIndex(row)}][section]`" value="data_pengajuan">
                                    <select class="form-select form-select-sm" :name="`fields[${globalIndex(row)}][field_type]`" x-model="row.field_type">
                                        <option value="text">Text</option><option value="number">Number</option><option value="date">Date</option><option value="dropdown">Dropdown</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="hidden" :name="`fields[${globalIndex(row)}][is_required]`" value="0">
                                    <input type="checkbox" :name="`fields[${globalIndex(row)}][is_required]`" value="1" x-model="row.is_required">
                                </td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" @click="removeRow('data_pengajuan', i)">Hapus</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- DATA FINANCIAL --}}
        <div class="card mb-3">
            <div class="card-header fw-bold d-flex justify-content-between">
                Data Financial
                <button type="button" class="btn btn-sm btn-outline-success" @click="addRow('data_financial')">+ Tambah Baris</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Field Label</th><th>Field Name</th><th>Kode COA</th><th>Tipe Hitung</th><th>Default</th><th></th></tr></thead>
                    <tbody>
                        <template x-for="(row, i) in financialRows" :key="row._id">
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" :name="`fields[${globalIndex(row)}][field_label]`" x-model="row.field_label" @input="row.field_name = toSnake(row.field_label)" required></td>
                                <td><input type="text" class="form-control form-control-sm" :name="`fields[${globalIndex(row)}][field_name]`" x-model="row.field_name"></td>
                                <td>
                                    <input type="hidden" :name="`fields[${globalIndex(row)}][section]`" value="data_financial">
                                    <select class="form-select form-select-sm" :name="`fields[${globalIndex(row)}][account_code]`" x-model="row.account_code">
                                        <option value="">- Tanpa COA -</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->code }}">{{ $acc->code }} – {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" :name="`fields[${globalIndex(row)}][calculation_type]`" x-model="row.calculation_type">
                                        <option value="fixed">Fixed</option><option value="percentage">Persentase</option>
                                    </select>
                                </td>
                                <td><input type="number" step="0.0001" class="form-control form-control-sm" :name="`fields[${globalIndex(row)}][default_value]`" x-model="row.default_value" placeholder="0"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" @click="removeRow('data_financial', i)">Hapus</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update Template</button>
            <a href="{{ route('product_templates.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
<script>
function templateEditForm(debtorInit, submissionInit, financialInit) {
    let _uid = 0;
    const uid = () => ++_uid;
    const toRow = (tf) => ({
        _id: uid(),
        field_label: tf.field_label ?? '',
        field_name: tf.field_name ?? '',
        field_type: tf.field_type ?? 'text',
        is_required: !!tf.is_required,
        account_code: tf.account_code ?? '',
        calculation_type: tf.calculation_type ?? 'fixed',
        default_value: tf.default_value ?? '',
    });

    return {
        debtorRows: debtorInit.map(toRow),
        submissionRows: submissionInit.map(toRow),
        financialRows: financialInit.map(toRow),

        allRows() { return [...this.debtorRows, ...this.submissionRows, ...this.financialRows]; },

        globalIndex(row) { return this.allRows().findIndex(r => r._id === row._id); },

        addRow(section) {
            const row = { _id: uid(), field_label: '', field_name: '', field_type: 'text', is_required: false, account_code: '', calculation_type: 'fixed', default_value: '' };
            if (section === 'informasi_debitur') this.debtorRows.push(row);
            else if (section === 'data_pengajuan') this.submissionRows.push(row);
            else this.financialRows.push(row);
        },

        removeRow(section, i) {
            if (section === 'informasi_debitur') this.debtorRows.splice(i, 1);
            else if (section === 'data_pengajuan') this.submissionRows.splice(i, 1);
            else this.financialRows.splice(i, 1);
        },

        toSnake(str) {
            return str.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
        },
    };
}
</script>
@endsection

