@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">User Role Setting</h1>
        <a href="{{ route('register') }}" class="btn btn-primary">Register User</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            @if($users->isEmpty())
                <div class="p-3 text-muted">Belum ada user.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role Saat Ini</th>
                                <th style="width: 320px;">Ubah Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $row)
                                <tr>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->name }}</td>
                                    <td>{{ $row->email }}</td>
                                    <td>{{ $row->roleRelation?->name ?? '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('users.update_role', $row) }}" class="d-flex gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role_id" class="form-select form-select-sm" required>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" @selected((int) $row->role_id === (int) $role->id)>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
