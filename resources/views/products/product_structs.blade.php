@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">PAM</h1>
        <a href="{{ route('pam.create') }}" class="btn btn-primary">Tambah PAM</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Produk</th>
                    <th>Kantor Bayar</th>
                    <th>Plafond Min</th>
                    <th>Plafond Max</th>
                    <th>Tenor Max</th>
                    <th>Rate %</th>
                    <th>Provisi %</th>
                    <th>Usia Masuk Min</th>
                    <th>Usia Max</th>
                    <th>Admin %</th>
                    <th>Blokir</th>
                    <th>Taspen</th>
                    <th>Tata Laksana</th>
                    <th>Tata Laksana + %</th>
                    <th>Admin Angsuran %</th>
                    <th>DBR %</th>
                    <th>Asabri</th>
                    <th>Usia Masuk Max</th>
                    <th>Sort</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->produk }}</td>
                        <td>{{ $item->kantor_bayar }}</td>
                        <td>{{ $item->plafond_min }}</td>
                        <td>{{ $item->plafond_max }}</td>
                        <td>{{ $item->tenor_max }}</td>
                        <td>{{ $item->rate_percent }}</td>
                        <td>{{ $item->provisi_percent }}</td>
                        <td>{{ $item->usia_masuk_min }}</td>
                        <td>{{ $item->usia_max }}</td>
                        <td>{{ $item->admin_percent }}</td>
                        <td>{{ $item->blokir_angsuran }}</td>
                        <td>{{ $item->taspen }}</td>
                        <td>{{ $item->tata_laksana }}</td>
                        <td>{{ $item->tata_laksana_plus_percent }}</td>
                        <td>{{ $item->admin_angsuran_percent }}</td>
                        <td>{{ $item->dbr_percent }}</td>
                        <td>{{ $item->asabri }}</td>
                        <td>{{ $item->usia_masuk_max }}</td>
                        <td>{{ $item->sort_order }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('pam.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('pam.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin hapus data PAM ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="20" class="text-center">Belum ada data PAM.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $items->links() }}
    </div>
</div>
@endsection
