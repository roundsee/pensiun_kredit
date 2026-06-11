@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Edit Mapping: {{ $template->name }}</h1>
            <div class="text-muted small">Dokumen: {{ strtoupper($template->document_type) }} | View: {{ $template->generated_view_path ?: '-' }}</div>
        </div>
        <a href="{{ route('mail_merge.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('mail_merge.update', $template) }}" id="mapping-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="mappings" id="mappings-input" value="{{ old('mappings', $mappingsJson) }}">
        <input type="hidden" name="template_html" id="template-html-input" value="">

        <div class="row g-3">
            <div class="col-lg-3">
                <div class="card h-100">
                    <div class="card-header fw-semibold">Field Source</div>
                    <div class="card-body">
                        <label class="form-label">Pilih Table</label>
                        <select class="form-select mb-2" id="table-select">
                            <option value="">-- pilih table --</option>
                            @foreach($tableOptions as $option)
                                <option value="{{ $option['key'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>

                        <div class="small text-muted mb-2" id="selected-field-label">Belum ada field dipilih.</div>
                        <div class="border rounded p-2" style="max-height: 65vh; overflow: auto;" id="field-list"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Preview Template (blok teks, lalu jadikan slot)</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-create-slot">Jadikan Slot dari Blok Teks</button>
                            <button type="button" class="btn btn-sm btn-outline-warning" id="btn-remove-slot">Hapus Slot Terpilih</button>
                        </div>
                    </div>
                    <div class="card-body" style="max-height: 75vh; overflow: auto; background: #f8f9fa;">
                        <div id="template-preview" class="bg-white border rounded p-3">{!! $template->template_html !!}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card h-100">
                    <div class="card-header fw-semibold">Summary Mapping</div>
                    <div class="card-body d-flex flex-column">
                        <div class="small text-muted mb-2" id="mapping-stats">0 mapping tersimpan.</div>
                        <div class="border rounded p-2 flex-grow-1" style="max-height: 60vh; overflow: auto;" id="mapping-summary"></div>
                        <div class="d-grid gap-2 mt-2">
                            <button type="button" class="btn btn-outline-danger" id="btn-clear-mappings">Hapus Semua Mapping</button>
                            <button type="submit" class="btn btn-primary">Simpan Mail Merge</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    #template-preview .merge-slot {
        display: inline-block;
        min-width: 120px;
        padding: 1px 6px;
        margin: 0 2px;
        border: 1px dashed #0d6efd;
        border-radius: 4px;
        background: #e7f1ff;
        color: #0a58ca;
        cursor: pointer;
        font-size: 0.9em;
    }

    #template-preview .merge-slot.active-slot {
        border-color: #198754;
        background: #d1e7dd;
        color: #0f5132;
    }

    #template-preview .merge-slot.unmapped {
        border-color: #6c757d;
        background: #f1f3f5;
        color: #495057;
    }
</style>

<script>
    (function () {
        const tableSelect = document.getElementById('table-select');
        const fieldList = document.getElementById('field-list');
        const selectedFieldLabel = document.getElementById('selected-field-label');
        const preview = document.getElementById('template-preview');
        const mappingsInput = document.getElementById('mappings-input');
        const templateHtmlInput = document.getElementById('template-html-input');
        const mappingSummary = document.getElementById('mapping-summary');
        const mappingStats = document.getElementById('mapping-stats');
        const clearMappingsBtn = document.getElementById('btn-clear-mappings');
        const createSlotBtn = document.getElementById('btn-create-slot');
        const removeSlotBtn = document.getElementById('btn-remove-slot');

        let selectedTable = '';
        let selectedField = '';
        let selectedFieldLabelText = '';
        let selectedSlotId = '';
        let mappings = {};

        try {
            mappings = JSON.parse(mappingsInput.value || '{}') || {};
        } catch (_e) {
            mappings = {};
        }

        function renderFieldList(fields) {
            fieldList.innerHTML = '';
            if (!fields.length) {
                fieldList.innerHTML = '<div class="text-muted small">Tidak ada field.</div>';
                return;
            }

            fields.forEach((field) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-primary w-100 text-start mb-1';
                btn.textContent = field;
                btn.addEventListener('click', function () {
                    selectedField = field;
                    selectedFieldLabelText = field;
                    selectedFieldLabel.textContent = `Field aktif: ${selectedTable}.${selectedFieldLabelText}`;
                });
                fieldList.appendChild(btn);
            });
        }

        async function loadFields() {
            selectedTable = tableSelect.value;
            selectedField = '';
            selectedFieldLabelText = '';
            selectedFieldLabel.textContent = 'Belum ada field dipilih.';

            if (!selectedTable) {
                renderFieldList([]);
                return;
            }

            const res = await fetch(`{{ route('mail_merge.fields') }}?table=${encodeURIComponent(selectedTable)}`);
            const data = await res.json();
            renderFieldList(data.fields || []);
        }

        function updateSlotVisual(slotEl) {
            const slotId = slotEl.dataset.slot;
            const mapping = mappings[slotId];

            slotEl.classList.toggle('active-slot', selectedSlotId === slotId);
            if (mapping && mapping.table && mapping.field) {
                slotEl.classList.remove('unmapped');
                slotEl.textContent = '{{' + mapping.table + '.' + mapping.field + '}}';
            } else {
                slotEl.classList.add('unmapped');
                slotEl.textContent = '[ pilih field ]';
            }
        }

        function refreshAllSlots() {
            preview.querySelectorAll('.merge-slot').forEach((slotEl) => {
                updateSlotVisual(slotEl);
            });
        }

        function getNextSlotId() {
            let max = 0;
            preview.querySelectorAll('.merge-slot').forEach((slotEl) => {
                const value = String(slotEl.dataset.slot || '');
                const match = value.match(/slot_(\d+)$/);
                if (!match) {
                    return;
                }

                const number = Number(match[1]);
                if (number > max) {
                    max = number;
                }
            });

            return `slot_${max + 1}`;
        }

        function bindSlotHandlers(slotEl) {
            if (!slotEl || slotEl.dataset.bound === '1') {
                return;
            }

            slotEl.dataset.bound = '1';
            slotEl.addEventListener('click', function () {
                selectedSlotId = slotEl.dataset.slot;

                if (selectedTable && selectedField) {
                    mappings[selectedSlotId] = {
                        table: selectedTable,
                        field: selectedField,
                    };
                }

                refreshAllSlots();
                refreshSummary();
                persistMappings();
            });
        }

        function bindAllSlots() {
            preview.querySelectorAll('.merge-slot').forEach((slotEl) => {
                bindSlotHandlers(slotEl);
            });
        }

        function refreshSummary() {
            mappingSummary.innerHTML = '';
            const entries = Object.entries(mappings).filter(([, value]) => value && value.table && value.field);

            mappingStats.textContent = `${entries.length} mapping tersimpan.`;

            if (!entries.length) {
                mappingSummary.innerHTML = '<div class="text-muted small">Belum ada slot yang di-mapping.</div>';
                return;
            }

            entries.forEach(([slotId, mapping]) => {
                const row = document.createElement('div');
                row.className = 'small border rounded p-2 mb-2';
                row.innerHTML = `<div><strong>${slotId}</strong></div><div>${mapping.table}.${mapping.field}</div>`;
                mappingSummary.appendChild(row);
            });
        }

        function persistMappings() {
            mappingsInput.value = JSON.stringify(mappings);
        }

        function createSlotFromSelection() {
            const selection = window.getSelection();
            if (!selection || selection.rangeCount === 0) {
                alert('Silakan blok teks di preview template terlebih dahulu.');
                return;
            }

            const range = selection.getRangeAt(0);
            if (range.collapsed) {
                alert('Silakan blok teks/angka yang ingin dijadikan slot.');
                return;
            }

            if (!preview.contains(range.commonAncestorContainer)) {
                alert('Blok teks harus berada di area preview template.');
                return;
            }

            const slotId = getNextSlotId();
            const slot = document.createElement('span');
            slot.className = 'merge-slot unmapped';
            slot.dataset.slot = slotId;
            slot.textContent = '[ pilih field ]';

            const extracted = range.extractContents();
            slot.appendChild(extracted);
            range.insertNode(slot);

            selection.removeAllRanges();
            selectedSlotId = slotId;
            mappings[slotId] = mappings[slotId] || { table: '', field: '' };

            bindSlotHandlers(slot);
            refreshAllSlots();
            refreshSummary();
            persistMappings();
        }

        function removeSelectedSlot() {
            if (!selectedSlotId) {
                alert('Pilih slot yang ingin dihapus terlebih dahulu.');
                return;
            }

            const slotEl = preview.querySelector(`.merge-slot[data-slot="${selectedSlotId}"]`);
            if (!slotEl) {
                return;
            }

            const plainText = document.createTextNode(slotEl.textContent || '');
            slotEl.replaceWith(plainText);

            delete mappings[selectedSlotId];
            selectedSlotId = '';

            refreshAllSlots();
            refreshSummary();
            persistMappings();
        }

        clearMappingsBtn.addEventListener('click', function () {
            if (!confirm('Hapus semua mapping pada template ini?')) {
                return;
            }

            mappings = {};
            selectedSlotId = '';
            refreshAllSlots();
            refreshSummary();
            persistMappings();
        });

        createSlotBtn.addEventListener('click', createSlotFromSelection);
        removeSlotBtn.addEventListener('click', removeSelectedSlot);

        document.getElementById('mapping-form').addEventListener('submit', function () {
            templateHtmlInput.value = preview.innerHTML;
            persistMappings();
        });

        tableSelect.addEventListener('change', loadFields);

        bindAllSlots();
        refreshAllSlots();
        refreshSummary();
        persistMappings();
    })();
</script>
@endsection