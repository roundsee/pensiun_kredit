@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Daftar Product Templates</h1>
    <a href="{{ route('product_templates.create') }}" class="btn btn-primary mb-3">Buat Template Baru</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Template</th>
                <th>Deskripsi</th>
                <th>Daftar Akun</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($templates as $template)
            <tr>
                <td>{{ $template->template_name }}</td>
                <td>{{ $template->template_description }}</td>
                <td>
                    <ul>
                        @foreach($template->templateAccounts as $acc)
                        <li>{{ $acc->name }} ({{ $acc->coa_code }}) - {{ $acc->formula_type }} @if($acc->percentage){{ $acc->percentage }}%@endif</li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    <a href="{{ route('product_templates.edit', $template) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('product_templates.destroy', $template) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Yakin ingin menghapus template ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $templates->links() }}
</div>
@endsection
