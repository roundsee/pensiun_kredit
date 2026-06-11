@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detail Produk</h1>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">{{ $product->name }}</h5>
            <p class="card-text">{{ $product->description }}</p>
            <p class="card-text"><strong>Status:</strong> {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</p>
        </div>
    </div>
    <h3>Template Akun</h3>
    @foreach($product->templates as $template)
        <div class="mb-3">
            <h5>{{ $template->name }}</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>COA Code</th>
                        <th>Nama Akun</th>
                        <th>Tipe Formula</th>
                        <th>Persentase</th>
                        <th>Min</th>
                        <th>Max</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($template->templateAccounts as $account)
                    <tr>
                        <td>{{ $account->coa_code }}</td>
                        <td>{{ $account->name }}</td>
                        <td>{{ $account->formula_type }}</td>
                        <td>{{ $account->percentage }}</td>
                        <td>{{ $account->min_value }}</td>
                        <td>{{ $account->max_value }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
