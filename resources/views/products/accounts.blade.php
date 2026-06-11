@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('accounts.create') }}" class="bg-green-500 text-white px-4 py-2 rounded">Tambah Akun</a>
    </div>
    @if($accounts->isEmpty())
        <p>Tidak ada data akun.</p>
    @else
        <table class="table-auto w-full border mt-4">
            <thead>
                <tr>
                    <th class="border px-4 py-2">Kode</th>
                    <th class="border px-4 py-2">Nama</th>
                    <th class="border px-4 py-2">Tipe</th>
                    <th class="border px-4 py-2">Group</th>
                    <th class="border px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $account)
                    <tr>
                        <td class="border px-4 py-2">{{ $account->code }}</td>
                        <td class="border px-4 py-2">{{ $account->name }}</td>
                        <td class="border px-4 py-2">{{ $account->type }}</td>
                        <td class="border px-4 py-2">{{ $account->group->name ?? '-' }}</td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('accounts.edit', $account->id) }}" class="bg-yellow-400 text-white px-2 py-1 rounded mr-2">Edit</a>
                            <form action="{{ route('accounts.destroy', $account->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Yakin hapus akun ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
