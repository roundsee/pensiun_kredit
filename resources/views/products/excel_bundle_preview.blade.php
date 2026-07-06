@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Preview Excel Bundle</h1>
            <div class="text-muted">
                Data Simulasi #{{ $dataSimulasi->id }} - {{ $dataSimulasi->nama_debitur ?: '-' }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('data_simulasi.list') }}" class="btn btn-outline-secondary">Kembali</a>
            @if($allValid)
                <a href="{{ route('excel_bundle.download', ['data_simulasi_id' => $dataSimulasi->id]) }}" class="btn btn-success">
                    Download Excel Bundle
                </a>
            @else
                <button type="button" class="btn btn-success" disabled>Download Excel Bundle</button>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($allValid)
        <div class="alert alert-success">
            Semua sheet valid. Excel bundle siap digenerate.
        </div>
    @else
        <div class="alert alert-warning">
            Masih ada data yang kosong. Lengkapi dulu semua field wajib, lalu generate Excel bundle.
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70px;">No</th>
                            <th>Sheet</th>
                            <th style="width: 160px;">Status</th>
                            <th>Field Wajib yang Kosong</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($validationRows as $index => $row)
                            @php
                                $isFocused = !empty($focus) && $focus === $row['focus_key'];
                                $isValid = empty($row['missing_fields']);
                            @endphp
                            <tr @if($isFocused) class="table-info" @endif>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['sheet_name'] }}</td>
                                <td>
                                    @if($isValid)
                                        <span class="badge bg-success">VALID</span>
                                    @else
                                        <span class="badge bg-danger">BELUM VALID</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isValid)
                                        <span class="text-muted">-</span>
                                    @else
                                        {{ implode(', ', $row['missing_fields']) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header fw-bold">Preview Data per Sheet (HTML)</div>
        <div class="card-body">
            @foreach($validationRows as $index => $row)
                @php
                    $isFocused = !empty($focus) && $focus === $row['focus_key'];
                    $isValid = empty($row['missing_fields']);
                    $previewRows = $previewRowsByFocus[$row['focus_key']] ?? [];
                @endphp

                <div class="border rounded p-3 mb-3 @if($isFocused) border-info @endif">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold">{{ $index + 1 }}. {{ $row['sheet_name'] }}</div>
                        @if($isValid)
                            <span class="badge bg-success">VALID</span>
                        @else
                            <span class="badge bg-danger">BELUM VALID</span>
                        @endif
                    </div>

                    @if(!$isValid)
                        <div class="alert alert-warning mb-0">
                            Preview belum ditampilkan karena data wajib masih kosong: {{ implode(', ', $row['missing_fields']) }}
                        </div>
                    @elseif(empty($previewRows))
                        <div class="text-muted">Tidak ada data untuk ditampilkan.</div>
                    @else
                        <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">No</th>
                                            <th style="width: 260px;">Label</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previewRows as $previewIndex => $previewRow)
                                        <tr>
                                            <td>{{ $previewIndex + 1 }}</td>
                                                <td>
                                                    <div class="fw-semibold">{{ $previewRow['label'] }}</div>
                                                </td>
                                            <td style="white-space: pre-wrap; word-break: break-word;">{{ $previewRow['value'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
