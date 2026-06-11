@extends('layouts.app')

@section('content')
<div class="container">
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
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Edit Data Simulasi #{{ $dataSimulasi->id }}</h1>
        <a href="{{ route('data_simulasi.list') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('data_simulasi.update', $dataSimulasi) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    @foreach($fields as $field)
                        @php
                            $isDateField = isset($dateFields[$field]);
                            $isNumericField = isset($numericFields[$field]);
                            $inputValue = old($field, $dataSimulasi->{$field});

                            if ($isDateField) {
                                $inputValue = $formatDateInputValue($inputValue);
                            } elseif ($isNumericField) {
                                $inputValue = $formatNumericInputValue($inputValue);
                            }
                        @endphp
                        <div class="col-md-6">
                            <label class="form-label">{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                            <input
                                type="{{ $isDateField ? 'date' : ($isNumericField ? 'number' : 'text') }}"
                                name="{{ $field }}"
                                class="form-control"
                                value="{{ $inputValue }}"
                                @if($isNumericField)
                                    step="any"
                                    inputmode="decimal"
                                @endif
                            >
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('data_simulasi.list') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection