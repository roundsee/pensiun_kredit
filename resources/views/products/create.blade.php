@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Buat Produk Baru</h2>
    <form method="POST" action="{{ route('products.store') }}" x-data="productForm()">
        @csrf

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-50 p-3 text-red-700">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-4">
            <label class="block font-semibold mb-1">Nama Produk</label>
            <input type="text" name="name" class="form-input w-full" required value="{{ old('name') }}">
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Deskripsi</label>
            <textarea name="description" class="form-textarea w-full">{{ old('description') }}</textarea>
        </div>
        <div class="mb-4">
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" checked class="form-checkbox">
                <span class="ml-2">Aktif</span>
            </label>
        </div>

        <div class="mb-6">
            <h3 class="font-semibold mb-2">Load Template (Opsional)</h3>
            <div class="flex gap-2">
                <select x-model="selectedTemplateId" class="form-select w-full">
                    <option value="">-- Pilih Template --</option>
                    @foreach(($templates ?? []) as $template)
                        <option value="{{ $template->id }}">{{ $template->template_name }}</option>
                    @endforeach
                </select>
                <button type="button" @click="loadTemplate" class="px-3 py-2 bg-blue-600 text-white rounded">Load Template</button>
            </div>
            <p class="text-sm text-gray-600 mt-2">Setelah load, Anda tetap bisa tambah atau hapus item.</p>
        </div>

        <div class="mb-6">
            <h3 class="font-semibold mb-2">Data Informasi Debitur & Pengajuan</h3>
            <template x-for="(field, index) in fields" :key="index">
                <div class="grid grid-cols-12 gap-2 mb-2 items-end">
                    <div class="col-span-3">
                        <label class="text-xs text-gray-600">Field Name</label>
                        <input type="text" :name="`fields[${index}][field_name]`" x-model="field.field_name" class="form-input w-full" required>
                    </div>
                    <div class="col-span-3">
                        <label class="text-xs text-gray-600">Field Label</label>
                        <input type="text" :name="`fields[${index}][field_label]`" x-model="field.field_label" class="form-input w-full" required>
                    </div>
                    <div class="col-span-2">
                        <label class="text-xs text-gray-600">Type</label>
                        <select :name="`fields[${index}][field_type]`" x-model="field.field_type" class="form-select w-full" required>
                            <option value="text">text</option>
                            <option value="number">number</option>
                            <option value="date">date</option>
                            <option value="dropdown">dropdown</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="text-xs text-gray-600">Group</label>
                        <select :name="`fields[${index}][group]`" x-model="field.group" class="form-select w-full" required>
                            <option value="informasi_debitur">informasi_debitur</option>
                            <option value="data_pengajuan">data_pengajuan</option>
                        </select>
                    </div>
                    <div class="col-span-1 text-center">
                        <label class="text-xs text-gray-600">Wajib</label>
                        <input type="hidden" :name="`fields[${index}][is_required]`" value="0">
                        <input type="checkbox" :name="`fields[${index}][is_required]`" value="1" x-model="field.is_required" class="form-checkbox">
                    </div>
                    <div class="col-span-1 text-right">
                        <button type="button" @click="removeField(index)" class="text-red-500 hover:underline">Hapus</button>
                    </div>
                </div>
            </template>
            <button type="button" @click="addField" class="mt-2 px-3 py-1 bg-slate-700 text-white rounded">Tambah Field</button>
        </div>

        <div class="mb-6">
            <h3 class="font-semibold mb-2">Data Financial & Mapping COA</h3>
            <template x-for="(item, index) in financials" :key="index">
                <div class="grid grid-cols-12 gap-2 mb-2 items-end">
                    <div class="col-span-2">
                        <label class="text-xs text-gray-600">Item</label>
                        <input type="text" :name="`financials[${index}][item_name]`" x-model="item.item_name" class="form-input w-full" required>
                    </div>
                    <div class="col-span-3">
                        <label class="text-xs text-gray-600">COA</label>
                        <select :name="`financials[${index}][account_id]`" x-model="item.account_id" class="form-select w-full" required>
                            <option value="">-- Pilih COA --</option>
                            <template x-for="coa in accounts" :key="coa.id">
                                <option :value="coa.id" x-text="`${coa.code} - ${coa.name}`"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="text-xs text-gray-600">Calc</label>
                        <select :name="`financials[${index}][calculation_type]`" x-model="item.calculation_type" class="form-select w-full" required>
                            <option value="percentage">percentage</option>
                            <option value="fixed">fixed</option>
                        </select>
                    </div>
                    <div class="col-span-1">
                        <label class="text-xs text-gray-600">Nilai</label>
                        <input type="number" step="0.01" :name="`financials[${index}][default_value]`" x-model="item.default_value" class="form-input w-full" required>
                    </div>
                    <div class="col-span-2">
                        <label class="text-xs text-gray-600">Tx Type</label>
                        <select :name="`financials[${index}][transaction_type]`" x-model="item.transaction_type" class="form-select w-full" required>
                            <option value="debit">debit</option>
                            <option value="credit">credit</option>
                        </select>
                    </div>
                    <div class="col-span-1 text-center">
                        <label class="text-xs text-gray-600">Potong</label>
                        <input type="hidden" :name="`financials[${index}][is_deducted_at_disbursement]`" value="0">
                        <input type="checkbox" :name="`financials[${index}][is_deducted_at_disbursement]`" value="1" x-model="item.is_deducted_at_disbursement" class="form-checkbox">
                    </div>
                    <div class="col-span-1 text-center">
                        <label class="text-xs text-gray-600">Simulasi</label>
                        <input type="hidden" :name="`financials[${index}][is_included_in_simulation]`" value="0">
                        <input type="checkbox" :name="`financials[${index}][is_included_in_simulation]`" value="1" x-model="item.is_included_in_simulation" class="form-checkbox">
                    </div>
                    <div class="col-span-12 text-right">
                        <button type="button" @click="removeFinancial(index)" class="text-red-500 hover:underline">Hapus</button>
                    </div>
                </div>
            </template>
            <button type="button" @click="addFinancial" class="mt-2 px-3 py-1 bg-slate-700 text-white rounded">Tambah Financial</button>
        </div>

        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Simpan Produk</button>
    </form>
</div>
<script type="application/json" id="templates-data">@json($templates ?? [])</script>
<script type="application/json" id="accounts-data">@json($accounts ?? [])</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function productForm() {
    return {
        templates: JSON.parse(document.getElementById('templates-data')?.textContent || '[]'),
        accounts: JSON.parse(document.getElementById('accounts-data')?.textContent || '[]'),
        selectedTemplateId: '',
        fields: [],
        financials: [],
        addField() {
            this.fields.push({
                field_name: '',
                field_label: '',
                field_type: 'text',
                is_required: true,
                group: 'informasi_debitur',
            });
        },
        removeField(index) {
            this.fields.splice(index, 1);
        },
        addFinancial() {
            this.financials.push({
                item_name: '',
                account_id: '',
                calculation_type: 'fixed',
                default_value: 0,
                transaction_type: 'credit',
                is_deducted_at_disbursement: true,
                is_included_in_simulation: true,
            });
        },
        removeFinancial(index) {
            this.financials.splice(index, 1);
        },
        loadTemplate() {
            if (!this.selectedTemplateId) {
                return;
            }

            fetch(`/product-templates/${this.selectedTemplateId}/load-items`)
                .then(res => res.json())
                .then(data => {
                    this.fields = data.fields || [];
                    this.financials = (data.financials || []).map(item => ({
                        item_name: item.item_name || '',
                        account_id: item.account_id || '',
                        calculation_type: item.calculation_type || 'fixed',
                        default_value: item.default_value || 0,
                        transaction_type: item.transaction_type || 'credit',
                        is_deducted_at_disbursement: item.is_deducted_at_disbursement ?? true,
                        is_included_in_simulation: item.is_included_in_simulation ?? true,
                    }));
                });
        },
    }
}
</script>
@endsection
