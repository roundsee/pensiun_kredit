@extends('layouts.app')

@section('content')
<div class="container max-w-lg mx-auto">
    <h1 class="text-xl font-bold mb-4">Edit Akun</h1>
    <form method="POST" action="{{ route('accounts.update', $account->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="block">Kode Akun</label>
            <input type="text" name="code" class="form-input w-full" value="{{ $account->code }}" required>
        </div>
        <div class="mb-3">
            <label class="block">Nama Akun</label>
            <input type="text" name="name" class="form-input w-full" value="{{ $account->name }}" required>
        </div>
        <div class="mb-3">
            <label class="block">Tipe</label>
            <input type="text" name="type" class="form-input w-full" value="{{ $account->type }}" required>
        </div>
        <div class="mb-3">
            <label class="block">Group</label>
            <select name="account_group_id" class="form-select w-full" required>
                <option value="">Pilih Group</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}" @if($account->account_group_id == $group->id) selected @endif>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
        <a href="{{ route('accounts.index') }}" class="ml-2 text-gray-600">Batal</a>
    </form>
</div>
@endsection
