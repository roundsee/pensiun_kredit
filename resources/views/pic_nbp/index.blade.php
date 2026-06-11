@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Data Petugas NBP (PIC)</h4>
        <a href="{{ route('pic_nbp.create') }}" class="btn btn-primary btn-sm">+ Tambah</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Nama Petugas NBP</th>
                    <th>Jabatan Petugas</th>
                    <th>Nomor Substitusi PIC</th>
                    <th>Tanggal Substitusi</th>
                    <th style="width:130px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row->nama_petugas }}</td>
                        <td>{{ $row->jabatan }}</td>
                        <td>{{ $row->nomor_substitusi }}</td>
                        <td>{{ $row->tanggal_substitusi }}</td>
                        <td>
                            <a href="{{ route('pic_nbp.edit', $row) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form method="POST" action="{{ route('pic_nbp.destroy', $row) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus data {{ addslashes($row->nama_petugas) }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
