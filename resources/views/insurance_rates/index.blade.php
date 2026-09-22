@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Insurance Rate</h1>
        <a href="{{ route('insurance_rates.create') }}" class="btn btn-primary">Tambah Insurance Rate</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Produk</th>
                    <th>Bank Tujuan</th>
                    <th>Jenis Rate</th>
                    <th>Tenor</th>
                    <th>Usia</th>
                    <th>Premium / 1jt</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->product }}</td>
                        <td>{{ $item->bank_tujuan ?? '-' }}</td>
                        <td>{{ filled($item->usia) ? 'Usia' : 'Tenor' }}</td>
                        <td>{{ $item->tenor ?? '-' }}</td>
                        <td>{{ $item->usia ?? '-' }}</td>
                        <td>{{ $item->premium_per_million }}</td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('insurance_rates.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('insurance_rates.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin hapus data Insurance Rate ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data Insurance Rate.</td>
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
