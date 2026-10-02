@php
    $name = $field['name'];
    $label = $field['label'];
    $type = $field['type'];
    $value = old($name, $debitur->{$name});

    if ($value instanceof \DateTimeInterface) {
        $value = $value->format('Y-m-d');
    } elseif ($type === 'number' && $value !== null && $value !== '') {
        $value = (float) $value;
    }

    $isWilayah = $type === 'select' && isset($field['wilayah']);
    $isAnakWilayah = $isWilayah && isset($field['parent']);
    $hasError = $errors->has($name);
@endphp

<div class="col-md-{{ $field['col'] ?? 6 }}">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($field['required'] ?? false)
            <span class="text-danger">*</span>
        @endif
    </label>

    @if($type === 'textarea')
        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            rows="2"
            class="form-control @if($hasError) is-invalid @endif"
            maxlength="{{ $field['maxlength'] ?? 255 }}"
            @if($field['required'] ?? false) required @endif
        >{{ $value }}</textarea>

    @elseif($type === 'select')
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            class="form-select @if($hasError) is-invalid @endif"
            @if($field['required'] ?? false) required @endif
            @if($field['readonly'] ?? false) readonly @endif
            @if($isWilayah)
                data-wilayah-group="{{ $field['group'] }}"
                data-wilayah-tier="{{ $field['wilayah'] }}"
                @if($isAnakWilayah) data-wilayah-parent="{{ $field['parent'] }}" @endif
            @endif
        >
            <option value="">-- Pilih --</option>

            @if($isAnakWilayah)
                {{-- Opsi anak diisi oleh JS dari parent yang terpilih. --}}
                @if($value !== null && $value !== '')
                    <option value="{{ $value }}" selected>{{ $value }}</option>
                @endif
            @else
                @foreach($field['options'] as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $value)>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            @endif
        </select>

    @else
        <input
            type="{{ $type === 'number' ? 'number' : ($type === 'date' ? 'date' : 'text') }}"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ $value }}"
            class="form-control @if($hasError) is-invalid @endif"
            @if($type === 'number') step="any" @endif
            @if(isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
            @if($field['required'] ?? false) required @endif
            @if($field['readonly'] ?? false) readonly @endif
        >
    @endif

    @if($hasError)
        <div class="invalid-feedback">{{ $errors->first($name) }}</div>
    @endif
</div>
