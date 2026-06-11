@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0">Input Data Pelengkap</h1>
            <div class="text-muted">Data simulasi #{{ $dataSimulasi->id }} - {{ $dataSimulasi->nama_debitur ?: '-' }}</div>
        </div>
        <a href="{{ route('data_simulasi.list') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $dateFields = array_flip($dateFields ?? []);
        $numericFields = array_flip($numericFields ?? []);

        $formatDateInputValue = static function ($value) {
            if ($value === null) {
                return '';
            }

            $text = trim((string) $value);
            if ($text === '') {
                return '';
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
                return $text;
            }

            foreach (['d/m/Y', 'd-m-Y', 'Y/m/d', 'Y-m-d H:i:s'] as $format) {
                try {
                    return \Illuminate\Support\Carbon::createFromFormat($format, $text)->format('Y-m-d');
                } catch (\Throwable $e) {
                    // Try the next format.
                }
            }

            try {
                return \Illuminate\Support\Carbon::parse($text)->format('Y-m-d');
            } catch (\Throwable $e) {
                return $text;
            }
        };

        $formatNumericInputValue = static function ($value) {
            if ($value === null) {
                return '';
            }

            $text = trim((string) $value);
            if ($text === '') {
                return '';
            }

            $text = str_replace([' ', 'Rp.', 'Rp', '%'], '', $text);

            if (str_contains($text, ',') && str_contains($text, '.')) {
                $text = str_replace('.', '', $text);
                $text = str_replace(',', '.', $text);
            } elseif (str_contains($text, ',')) {
                $text = str_replace(',', '.', $text);
            }

            return preg_replace('/[^\d\-\.]/', '', $text) ?? '';
        };

        $mappedAnyCount = 0;
        foreach ($fields as $field => $label) {
            $usage = $usageMap[$field] ?? ['pk' => false, 'sppk' => false];
            if ($usage['pk'] || $usage['sppk']) {
                $mappedAnyCount++;
            }
        }
    @endphp

    <div class="card mb-3 d-none">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Mapping Report Data Pelengkap</div>
            <div class="small text-muted">{{ $mappedAnyCount }} / {{ count($fields) }} field sudah dipakai</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%;">Field</th>
                            <th style="width: 35%;">Header</th>
                            <th style="width: 15%;">Perjanjian Kredit</th>
                            <th style="width: 15%;">SPPK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fields as $field => $label)
                            @php
                                $usage = $usageMap[$field] ?? ['pk' => false, 'sppk' => false];
                            @endphp
                            <tr>
                                <td><code>{{ $field }}</code></td>
                                <td>{{ $label }}</td>
                                <td>
                                    @if($usage['pk'])
                                        <span class="badge bg-success">Dipakai</span>
                                    @else
                                        <span class="badge bg-secondary">Belum</span>
                                    @endif
                                </td>
                                <td>
                                    @if($usage['sppk'])
                                        <span class="badge bg-success">Dipakai</span>
                                    @else
                                        <span class="badge bg-secondary">Belum</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('data_simulasi.pelengkap.update', $dataSimulasi) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="alert alert-info">
                    <div class="fw-semibold mb-1">Paste dari Excel / Google Sheet</div>
                    <div class="small mb-2">
                        Copy satu baris data dari Excel/Google Sheet, lalu paste di kotak berikut.
                        Sistem akan mengisi field sesuai urutan kolom data pelengkap.
                    </div>
                    <textarea id="paste-row"
                              class="form-control mb-2"
                              rows="3"
                              placeholder="Contoh: 2026-03-12<TAB>002/SPPK/...<TAB>Jember<TAB>Jl. ..."
                              data-fields="{{ implode(',', array_keys($fields)) }}"></textarea>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-apply-paste">Isi Otomatis dari Paste</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-clear-paste">Kosongkan Kotak Paste</button>
                    </div>
                    <div class="small mt-2 text-muted" id="paste-status"></div>
                </div>

                <div class="alert alert-warning">
                    <div class="fw-semibold mb-1">OCR PDF Pelengkap (proses & simpan otomatis)</div>
                    <div class="small mb-2">
                        Upload PDF sumber (contoh: MAEMUNAH_TERFLAGGING.PDF). Sistem akan memetakan field pelengkap sebanyak mungkin, simpan ke database, dan mengisi form ini.
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8">
                            <label for="ocr_pdf_file" class="form-label">File PDF</label>
                            <input type="file" class="form-control" id="ocr_pdf_file" accept="application/pdf">
                        </div>
                        <div class="col-md-4 d-grid">
                            <button type="button" class="btn btn-outline-dark" id="btn-ocr-pdf">Proses & Simpan OCR PDF</button>
                        </div>
                    </div>
                    <div class="small mt-2 text-muted" id="ocr-status"></div>
                </div>

                <div class="row g-3">
                    @foreach($fields as $field => $label)
                        @php
                            $isDateField = isset($dateFields[$field]);
                            $isNumericField = isset($numericFields[$field]);
                            $inputValue = old($field, $pelengkap?->{$field});

                            if ($isDateField) {
                                $inputValue = $formatDateInputValue($inputValue);
                            } elseif ($isNumericField) {
                                $inputValue = $formatNumericInputValue($inputValue);
                            }
                        @endphp
                        <div class="col-md-6">
                            <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                            <input
                                type="{{ $isDateField ? 'date' : ($isNumericField ? 'number' : 'text') }}"
                                class="form-control @error($field) is-invalid @enderror"
                                id="{{ $field }}"
                                name="{{ $field }}"
                                value="{{ $inputValue }}"
                                @if($isNumericField)
                                    step="any"
                                    inputmode="decimal"
                                @endif
                            >
                            @error($field)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('data_simulasi.list') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Data Pelengkap</button>
            </div>
        </div>
    </form>
</div>

<script>
    (function () {
        const pasteBox = document.getElementById('paste-row');
        const applyBtn = document.getElementById('btn-apply-paste');
        const clearBtn = document.getElementById('btn-clear-paste');
        const statusEl = document.getElementById('paste-status');
        const ocrStatusEl = document.getElementById('ocr-status');
        const ocrFileInput = document.getElementById('ocr_pdf_file');
        const ocrBtn = document.getElementById('btn-ocr-pdf');
        const dateFields = <?php echo json_encode(array_keys($dateFields ?? [])); ?>;
        const numericFields = <?php echo json_encode(array_keys($numericFields ?? [])); ?>;

        if (!pasteBox || !applyBtn || !clearBtn || !statusEl) {
            return;
        }

        const dateFieldSet = new Set(dateFields);
        const numericFieldSet = new Set(numericFields);

        const fieldOrder = (pasteBox.dataset.fields || '')
            .split(',')
            .map((item) => item.trim())
            .filter(Boolean);

        function setStatus(message, isError) {
            statusEl.textContent = message;
            statusEl.classList.toggle('text-danger', !!isError);
            statusEl.classList.toggle('text-success', !isError && message.length > 0);
        }

        function normalizeDateValue(value) {
            const text = (value || '').toString().trim();
            if (!text) {
                return '';
            }

            if (/^\d{4}-\d{2}-\d{2}$/.test(text)) {
                return text;
            }

            const match = text.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
            if (match) {
                const day = match[1].padStart(2, '0');
                const month = match[2].padStart(2, '0');
                const year = match[3];
                return `${year}-${month}-${day}`;
            }

            return text;
        }

        function normalizeNumericValue(value) {
            let text = (value || '').toString().trim();
            if (!text) {
                return '';
            }

            text = text.replace(/\s|Rp\.?|%/gi, '');

            if (text.includes(',') && text.includes('.')) {
                text = text.replace(/\./g, '').replace(',', '.');
            } else if (text.includes(',')) {
                text = text.replace(',', '.');
            }

            return text.replace(/[^\d\-\.]/g, '');
        }

        function normalizeFieldValue(field, value) {
            if (dateFieldSet.has(field)) {
                return normalizeDateValue(value);
            }

            if (numericFieldSet.has(field)) {
                return normalizeNumericValue(value);
            }

            return (value || '').toString().trim();
        }

        function getCandidateLine(text) {
            const lines = text
                .split(/\r?\n/)
                .map((line) => line.trim())
                .filter((line) => line.length > 0);

            if (lines.length === 0) {
                return '';
            }

            // Jika user paste header + data, pakai baris kedua.
            if (lines.length > 1 && /TANGGAL\s+SPPK|NO\s+SPPK|ALAMAT\s+2|NAMA\s+AO/i.test(lines[0])) {
                return lines[1];
            }

            return lines[0];
        }

        function applyPaste() {
            const raw = pasteBox.value || '';
            const candidateLine = getCandidateLine(raw);

            if (!candidateLine) {
                setStatus('Belum ada data yang dipaste.', true);
                return;
            }

            const values = candidateLine.split('\t');

            if (values.length < 2) {
                setStatus('Format tidak terbaca. Pastikan data dipaste dari Excel/Sheet (terpisah TAB).', true);
                return;
            }

            let filled = 0;
            fieldOrder.forEach((field, index) => {
                const input = document.getElementById(field);
                if (!input) {
                    return;
                }

                const nextValue = values[index] !== undefined ? normalizeFieldValue(field, values[index]) : '';
                if (nextValue !== '') {
                    filled += 1;
                }
                input.value = nextValue;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });

            setStatus(`Berhasil isi otomatis ${filled} field dari ${values.length} kolom paste.`, false);
        }

        applyBtn.addEventListener('click', applyPaste);

        clearBtn.addEventListener('click', function () {
            pasteBox.value = '';
            setStatus('', false);
            pasteBox.focus();
        });

        function setOcrStatus(message, isError) {
            if (!ocrStatusEl) {
                return;
            }

            ocrStatusEl.textContent = message;
            ocrStatusEl.classList.toggle('text-danger', !!isError);
            ocrStatusEl.classList.toggle('text-success', !isError && message.length > 0);
        }

        async function runOcrPdf() {
            if (!ocrFileInput || !ocrBtn) {
                return;
            }

            const file = ocrFileInput.files && ocrFileInput.files[0] ? ocrFileInput.files[0] : null;
            if (!file) {
                setOcrStatus('Pilih file PDF terlebih dahulu.', true);
                return;
            }

            const csrf = document.querySelector('input[name="_token"]');
            if (!csrf) {
                setOcrStatus('Token CSRF tidak ditemukan.', true);
                return;
            }

            const formData = new FormData();
            formData.append('pdf_file', file);

            ocrBtn.disabled = true;
            setOcrStatus('Memproses OCR PDF...', false);

            try {
                const response = await fetch("{{ route('data_simulasi.pelengkap.ocr_pdf', $dataSimulasi) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf.value,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const payload = await response.json();
                if (!response.ok) {
                    const message = payload.message || 'Gagal memproses OCR PDF.';
                    setOcrStatus(message, true);
                    return;
                }

                const fillMap = payload.data || {};
                let filled = 0;
                Object.keys(fillMap).forEach((field) => {
                    const input = document.getElementById(field);
                    if (!input) {
                        return;
                    }

                    const value = normalizeFieldValue(field, fillMap[field]);
                    if (value !== '') {
                        filled += 1;
                        input.value = value;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });

                setOcrStatus('OCR selesai. Field terisi otomatis: ' + filled + '.', false);
            } catch (error) {
                setOcrStatus('Terjadi kesalahan saat memproses OCR PDF.', true);
            } finally {
                ocrBtn.disabled = false;
            }
        }

        if (ocrBtn) {
            ocrBtn.addEventListener('click', runOcrPdf);
        }
    })();
</script>
@endsection