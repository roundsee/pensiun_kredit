@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-3">
        <a href="{{ route('pic_nbp.index') }}" class="btn btn-secondary btn-sm">&larr; Kembali</a>
    </div>

    <div class="card" style="max-width:560px">
        <div class="card-header">
            {{ $record ? 'Edit Data Petugas NBP' : 'Tambah Petugas NBP' }}
        </div>
        <div class="card-body">
            <form method="POST"
                  action="{{ $record ? route('pic_nbp.update', $record) : route('pic_nbp.store') }}">
                @csrf
                @if($record)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Petugas NBP <span class="text-danger">*</span></label>
                    <input type="text" name="nama_petugas" class="form-control @error('nama_petugas') is-invalid @enderror"
                           value="{{ old('nama_petugas', $record?->nama_petugas) }}" required>
                    @error('nama_petugas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jabatan Petugas</label>
                    <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror"
                           value="{{ old('jabatan', $record?->jabatan) }}">
                    @error('jabatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nomor Substitusi PIC</label>
                    <input type="text" name="nomor_substitusi" class="form-control @error('nomor_substitusi') is-invalid @enderror"
                           value="{{ old('nomor_substitusi', $record?->nomor_substitusi) }}"
                           placeholder="NBP.01.0030/PIC/II/2026">
                    @error('nomor_substitusi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal Substitusi</label>
                    <input type="text" name="tanggal_substitusi" class="form-control @error('tanggal_substitusi') is-invalid @enderror"
                           value="{{ old('tanggal_substitusi', $record?->tanggal_substitusi) }}"
                           placeholder="02/02/2026">
                    @error('tanggal_substitusi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
