@extends('layouts.app')

@section('content')
<style>
    .pam-table-scroll {
        max-height: 70vh;
        overflow: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
    }

    .pam-table-scroll table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .pam-table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        background: #f8f9fa;
        box-shadow: inset 0 -1px 0 #dee2e6;
    }

    .pam-table-scroll tbody th,
    .pam-table-scroll tbody td:first-child {
        position: sticky;
        left: 0;
        z-index: 1;
        background: #fff;
    }

    .pam-table-scroll thead th:first-child {
        left: 0;
        z-index: 4;
        background: #f8f9fa;
    }

    .pagination {
        margin-bottom: 0;
        gap: 0.25rem;
    }

    .pagination .page-item .page-link {
        padding: 0.3rem 0.55rem !important;
        font-size: 0.75rem !important;
        line-height: 1.1 !important;
        min-width: 2.1rem !important;
        border-radius: 0.3rem !important;
    }

    .pagination .page-item.active .page-link {
        font-weight: 600;
    }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">PAM</h1>
        <a href="{{ route('pam.create') }}" class="btn btn-primary">Tambah PAM</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="pam-table-scroll">
        <table class="table table-bordered table-striped align-middle mb-0">
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
        {{ $items->links('vendor.pagination.numeric-only') }}
    </div>
</div>
@endsection
