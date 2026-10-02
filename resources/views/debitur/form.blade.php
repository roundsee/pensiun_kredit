@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">Debitur Info</h1>
            <div class="text-muted">
                Nopen <strong>{{ $debitur->nopen ?: '-' }}</strong>
                @if($debitur->nama_ktp)
                    &middot; {{ $debitur->nama_ktp }}
                @endif
            </div>
        </div>
        <a href="{{ route('debitur.index') }}" class="btn btn-outline-secondary">Kembali ke List</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            Ada {{ $errors->count() }} field yang perlu diperbaiki. Form otomatis pindah ke tab yang bermasalah.
        </div>
    @endif

    <form method="POST" action="{{ route('debitur.update', ['nopen' => $debitur->nopen]) }}" id="debitur-form">
        @csrf
        @method('PUT')

        @include('debitur._tabs', [
            'debiturTabs' => $tabs,
            'debiturFieldMap' => $fieldMap,
        ])

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('debitur.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

@include('debitur._wilayah_js')
@endsection
