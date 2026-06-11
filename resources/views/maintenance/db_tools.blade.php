@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-3">Database Tools</h1>
    <p class="text-muted">Gunakan halaman ini hanya jika hosting tidak menyediakan akses CLI/SSH.</p>
    <div class="alert alert-info py-2">
        Untuk menghindari eksekusi massal, Anda bisa pilih file migration dan class seeder spesifik. Jika tidak dipilih, sistem menjalankan semua yang pending.
    </div>
    <div class="alert alert-warning py-2">
        Jika aplikasi berjalan di environment production (hosting), sistem otomatis menambahkan --force agar perintah tidak dibatalkan oleh prompt konfirmasi.
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('maintenance_error'))
        <div class="alert alert-danger">{{ session('maintenance_error') }}</div>
    @endif

    @if (session('maintenance_success'))
        <div class="alert alert-success">{{ session('maintenance_success') }}</div>
    @endif

    @if (session('maintenance_output'))
        <div class="alert alert-secondary">
            <strong>Output:</strong>
            <pre class="mb-0 mt-2" style="white-space: pre-wrap;">{{ session('maintenance_output') }}</pre>
        </div>
    @endif

    @if (session('query_success'))
        <div class="alert alert-success">{{ session('query_success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header fw-semibold">Query Runner</div>
        <div class="card-body">
            <form method="POST" action="{{ route('maintenance.db.query') }}">
                @csrf
                <div class="mb-3">
                    <label for="query_sql" class="form-label">SQL Query</label>
                    <textarea
                        id="query_sql"
                        name="query_sql"
                        class="form-control"
                        rows="6"
                        placeholder="SELECT * FROM data_simulasi LIMIT 10"
                        required
                    >{{ old('query_sql', request('query_sql')) }}</textarea>
                    <div class="form-text">Hanya query read-only yang diizinkan: SELECT, WITH, SHOW, DESCRIBE, EXPLAIN. Satu statement saja.</div>
                </div>

                <div class="mb-3">
                    <label for="query_token" class="form-label">Maintenance Token</label>
                    <input
                        type="password"
                        id="query_token"
                        name="maintenance_token"
                        class="form-control"
                        placeholder="Masukkan WEB_DB_MAINTENANCE_TOKEN"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-dark" onclick="return confirm('Jalankan query read-only ini?')">Execute</button>
            </form>
        </div>
    </div>

    @if (session('query_rows'))
        @php
            $queryColumns = session('query_columns', []);
            $queryRows = session('query_rows', []);
            $querySqlResult = session('query_sql_result', old('query_sql', ''));
        @endphp

        <div class="card mb-3">
            <div class="card-header fw-semibold">Result Table</div>
            <div class="card-body">
                @if (trim((string) $querySqlResult) !== '')
                    <div class="mb-3">
                        <div class="small text-muted mb-1">Executed SQL</div>
                        <pre class="bg-light p-2 mb-0" style="white-space: pre-wrap;">{{ $querySqlResult }}</pre>
                    </div>
                @endif

                @if (count($queryRows) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70px;">#</th>
                                    @foreach ($queryColumns as $column)
                                        <th>{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($queryRows as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        @foreach ($queryColumns as $column)
                                            <td>{{ data_get($row, $column) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-muted">Tidak ada baris hasil.</div>
                @endif
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST">
                @csrf
                <div class="mb-3">
                    <label for="maintenance_token" class="form-label">Maintenance Token</label>
                    <input
                        type="password"
                        id="maintenance_token"
                        name="maintenance_token"
                        class="form-control"
                        placeholder="Masukkan WEB_DB_MAINTENANCE_TOKEN"
                        required
                    >
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="force" name="force" checked>
                    <label class="form-check-label" for="force">
                        Jalankan dengan --force (opsional)
                    </label>
                </div>

                <div class="mb-3">
                    <label for="migration_file" class="form-label">Pilih File Migration (opsional)</label>
                    <select id="migration_file" name="migration_file" class="form-select">
                        <option value="">-- Semua migration pending --</option>
                        @foreach($migrationFiles as $migrationFile)
                            <option value="{{ $migrationFile }}">{{ $migrationFile }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="seeder_class" class="form-label">Pilih Seeder Class (opsional)</label>
                    <select id="seeder_class" name="seeder_class" class="form-select">
                        <option value="">-- DatabaseSeeder / default --</option>
                        @foreach($seederClasses as $seederClass)
                            <option value="{{ $seederClass }}">{{ $seederClass }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button
                        type="submit"
                        formaction="{{ route('maintenance.db.optimize_clear') }}"
                        class="btn btn-dark"
                        onclick="return confirm('Jalankan optimize:clear + reset OPcache sekarang?')"
                    >
                        Optimize Clear + Reset OPcache
                    </button>

                    <button
                        type="submit"
                        formaction="{{ route('maintenance.db.config_clear') }}"
                        class="btn btn-secondary"
                        onclick="return confirm('Jalankan config:clear sekarang?')"
                    >
                        Config Clear
                    </button>

                    <button
                        type="submit"
                        formaction="{{ route('maintenance.db.migrate') }}"
                        class="btn btn-warning"
                        onclick="return confirm('Jalankan migrate sekarang?')"
                    >
                        Jalankan Migrate
                    </button>

                    <button
                        type="submit"
                        formaction="{{ route('maintenance.db.seed') }}"
                        class="btn btn-primary"
                        onclick="return confirm('Jalankan db:seed sekarang?')"
                    >
                        Jalankan Seeder
                    </button>

                    <button
                        type="submit"
                        formaction="{{ route('maintenance.db.migrate_seed') }}"
                        class="btn btn-danger"
                        onclick="return confirm('Jalankan migrate --seed sekarang?')"
                    >
                        Migrate + Seeder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
