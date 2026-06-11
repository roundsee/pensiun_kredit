<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class DatabaseMaintenanceController extends Controller
{
    public function index()
    {
        return view('maintenance.db_tools', [
            'migrationFiles' => $this->getMigrationFiles(),
            'seederClasses' => $this->getSeederClasses(),
        ]);
    }

    public function migrate(Request $request): RedirectResponse
    {
        $migrationFiles = $this->getMigrationFiles();

        $request->validate([
            'migration_file' => ['nullable', 'string', 'in:' . implode(',', $migrationFiles)],
        ]);

        $arguments = [];
        $selectedMigration = (string) $request->input('migration_file', '');
        if ($selectedMigration !== '') {
            $arguments['--path'] = 'database/migrations/' . $selectedMigration;
        }

        return $this->runCommand($request, 'migrate', $arguments);
    }

    public function seed(Request $request): RedirectResponse
    {
        $seederClasses = $this->getSeederClasses();

        $request->validate([
            'seeder_class' => ['nullable', 'string', 'in:' . implode(',', $seederClasses)],
        ]);

        $arguments = [];
        $selectedSeeder = (string) $request->input('seeder_class', '');
        if ($selectedSeeder !== '') {
            $arguments['--class'] = $selectedSeeder;
        }

        return $this->runCommand($request, 'db:seed', $arguments);
    }

    public function migrateAndSeed(Request $request): RedirectResponse
    {
        $migrationFiles = $this->getMigrationFiles();
        $seederClasses = $this->getSeederClasses();

        $request->validate([
            'migration_file' => ['nullable', 'string', 'in:' . implode(',', $migrationFiles)],
            'seeder_class' => ['nullable', 'string', 'in:' . implode(',', $seederClasses)],
        ]);

        $arguments = ['--seed' => true];

        $selectedMigration = (string) $request->input('migration_file', '');
        if ($selectedMigration !== '') {
            $arguments['--path'] = 'database/migrations/' . $selectedMigration;
        }

        $selectedSeeder = (string) $request->input('seeder_class', '');
        if ($selectedSeeder !== '') {
            $arguments['--seeder'] = $selectedSeeder;
        }

        return $this->runCommand($request, 'migrate', $arguments);
    }

    public function optimizeClear(Request $request): RedirectResponse
    {
        if (!config('maintenance.enabled')) {
            return back()->with('maintenance_error', 'Fitur maintenance database dinonaktifkan. Aktifkan WEB_DB_MAINTENANCE_ENABLED=true di .env.');
        }

        $request->validate([
            'maintenance_token' => ['required', 'string'],
        ]);

        $expectedToken = (string) config('maintenance.token');
        if ($expectedToken === '') {
            return back()->with('maintenance_error', 'Token maintenance belum dikonfigurasi. Isi WEB_DB_MAINTENANCE_TOKEN di .env.');
        }

        if (!hash_equals($expectedToken, (string) $request->input('maintenance_token'))) {
            return back()->with('maintenance_error', 'Token maintenance tidak valid.');
        }

        try {
            Artisan::call('optimize:clear');
            $commandOutput = trim(Artisan::output());

            $opcacheMessage = 'OPcache tidak tersedia.';
            if (function_exists('opcache_reset')) {
                $opcacheMessage = opcache_reset()
                    ? 'OPcache berhasil di-reset.'
                    : 'OPcache tersedia tetapi gagal di-reset.';
            }

            $combinedOutput = $commandOutput;
            if ($combinedOutput !== '') {
                $combinedOutput .= PHP_EOL . PHP_EOL;
            }
            $combinedOutput .= $opcacheMessage;

            return back()->with([
                'maintenance_success' => 'Perintah berhasil dijalankan: optimize:clear',
                'maintenance_output' => $combinedOutput,
            ]);
        } catch (Throwable $exception) {
            Log::error('Web app maintenance optimize:clear failed.', [
                'error' => $exception->getMessage(),
            ]);

            return back()->with('maintenance_error', 'Gagal menjalankan optimize:clear: ' . $exception->getMessage());
        }
    }

    public function configClear(Request $request): RedirectResponse
    {
        if (!config('maintenance.enabled')) {
            return back()->with('maintenance_error', 'Fitur maintenance database dinonaktifkan. Aktifkan WEB_DB_MAINTENANCE_ENABLED=true di .env.');
        }

        $request->validate([
            'maintenance_token' => ['required', 'string'],
        ]);

        $expectedToken = (string) config('maintenance.token');
        if ($expectedToken === '') {
            return back()->with('maintenance_error', 'Token maintenance belum dikonfigurasi. Isi WEB_DB_MAINTENANCE_TOKEN di .env.');
        }

        if (!hash_equals($expectedToken, (string) $request->input('maintenance_token'))) {
            return back()->with('maintenance_error', 'Token maintenance tidak valid.');
        }

        try {
            Artisan::call('config:clear');
            $commandOutput = trim(Artisan::output());

            return back()->with([
                'maintenance_success' => 'Perintah berhasil dijalankan: config:clear',
                'maintenance_output' => $commandOutput ?: '(tidak ada output)',
            ]);
        } catch (Throwable $exception) {
            Log::error('Web app maintenance config:clear failed.', [
                'error' => $exception->getMessage(),
            ]);

            return back()->with('maintenance_error', 'Gagal menjalankan config:clear: ' . $exception->getMessage());
        }
    }

    public function executeQuery(Request $request)
    {
        if (!config('maintenance.enabled')) {
            return back()->with('maintenance_error', 'Fitur maintenance database dinonaktifkan. Aktifkan WEB_DB_MAINTENANCE_ENABLED=true di .env.');
        }

        $validated = $request->validate([
            'maintenance_token' => ['required', 'string'],
            'query_sql' => ['required', 'string', 'max:20000'],
        ]);

        $expectedToken = (string) config('maintenance.token');
        if ($expectedToken === '') {
            return back()->with('maintenance_error', 'Token maintenance belum dikonfigurasi. Isi WEB_DB_MAINTENANCE_TOKEN di .env.');
        }

        if (!hash_equals($expectedToken, (string) $validated['maintenance_token'])) {
            return back()->withInput()->with('maintenance_error', 'Token maintenance tidak valid.');
        }

        $sql = trim((string) $validated['query_sql']);
        if ($sql === '') {
            return back()->withInput()->with('maintenance_error', 'SQL query tidak boleh kosong.');
        }

        if (!$this->isReadOnlyQuery($sql)) {
            return back()->withInput()->with('maintenance_error', 'Hanya query read-only yang diizinkan: SELECT, WITH, SHOW, DESCRIBE, EXPLAIN.');
        }

        try {
            $startedAt = microtime(true);
            $rows = DB::select($sql);
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            $resultRows = array_map(static function ($row) {
                return (array) $row;
            }, $rows);

            $columns = [];
            if ($resultRows !== []) {
                $columns = array_keys($resultRows[0]);
            }

            return back()->withInput()->with([
                'query_success' => 'Query berhasil dijalankan. ' . count($resultRows) . ' baris ditemukan dalam ' . $durationMs . ' ms.',
                'query_columns' => $columns,
                'query_rows' => $resultRows,
                'query_sql_result' => $sql,
            ]);
        } catch (Throwable $exception) {
            Log::error('Web DB query runner failed.', [
                'sql' => $sql,
                'error' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('maintenance_error', 'Gagal menjalankan query: ' . $exception->getMessage());
        }
    }

    private function runCommand(Request $request, string $command, array $arguments): RedirectResponse
    {
        if (!config('maintenance.enabled')) {
            return back()->with('maintenance_error', 'Fitur maintenance database dinonaktifkan. Aktifkan WEB_DB_MAINTENANCE_ENABLED=true di .env.');
        }

        $request->validate([
            'maintenance_token' => ['required', 'string'],
            'force' => ['nullable', 'boolean'],
        ]);

        $expectedToken = (string) config('maintenance.token');
        if ($expectedToken === '') {
            return back()->with('maintenance_error', 'Token maintenance belum dikonfigurasi. Isi WEB_DB_MAINTENANCE_TOKEN di .env.');
        }

        if (!hash_equals($expectedToken, (string) $request->input('maintenance_token'))) {
            return back()->with('maintenance_error', 'Token maintenance tidak valid.');
        }

        $runtimeArguments = $arguments;
        $isProduction = app()->environment('production');
        $shouldForce = $request->boolean('force') || $isProduction;

        if ($shouldForce) {
            $runtimeArguments['--force'] = true;
        }

        try {
            Artisan::call($command, $runtimeArguments);

            $flagInfo = $shouldForce
                ? ($isProduction && !$request->boolean('force')
                    ? ' dengan --force (otomatis karena production)'
                    : ' dengan --force')
                : ' tanpa --force';
            $selectionInfo = $this->selectionInfo($runtimeArguments);

            return back()->with([
                'maintenance_success' => 'Perintah berhasil dijalankan: ' . $command . $flagInfo . $selectionInfo,
                'maintenance_output' => trim(Artisan::output()) ?: '(tidak ada output)',
            ]);
        } catch (Throwable $exception) {
            Log::error('Web DB maintenance command failed.', [
                'command' => $command,
                'arguments' => $arguments,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('maintenance_error', 'Gagal menjalankan perintah: ' . $exception->getMessage());
        }
    }

    /**
     * @return array<int, string>
     */
    private function getMigrationFiles(): array
    {
        $files = File::files(database_path('migrations'));

        return collect($files)
            ->map(fn ($file) => $file->getFilename())
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function getSeederClasses(): array
    {
        $files = File::files(database_path('seeders'));

        return collect($files)
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->filter(fn ($class) => $class !== 'DatabaseSeeder')
            ->sort()
            ->values()
            ->all();
    }

    private function selectionInfo(array $runtimeArguments): string
    {
        $info = [];

        if (isset($runtimeArguments['--path'])) {
            $info[] = 'migration: ' . basename((string) $runtimeArguments['--path']);
        }

        if (isset($runtimeArguments['--class'])) {
            $info[] = 'seeder: ' . (string) $runtimeArguments['--class'];
        }

        if (isset($runtimeArguments['--seeder'])) {
            $info[] = 'seeder: ' . (string) $runtimeArguments['--seeder'];
        }

        return $info === [] ? '' : ' [' . implode(', ', $info) . ']';
    }

    private function isReadOnlyQuery(string $sql): bool
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($sql)) ?? trim($sql);

        if ($normalized === '') {
            return false;
        }

        if (str_contains($normalized, ';')) {
            return false;
        }

        if (!preg_match('/^(select|with|show|describe|desc|explain)\b/i', $normalized)) {
            return false;
        }

        if (preg_match('/\b(insert|update|delete|drop|alter|truncate|create|replace|grant|revoke|call|execute|exec|set|use)\b/i', $normalized)) {
            return false;
        }

        return true;
    }
}
