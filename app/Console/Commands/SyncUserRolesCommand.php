<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncUserRolesCommand extends Command
{
    protected $signature = 'roles:sync-users {--clear-old : Clear users.role after sync} {--force : Overwrite existing role_id}';

    protected $description = 'Sync users.role (slug) into users.role_id using the roles table';

    public function handle(): int
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('users')) {
            $this->error('Required tables `roles` or `users` do not exist.');
            return self::FAILURE;
        }

        $this->info('Building role slug -> id map...');
        $roles = DB::table('roles')->pluck('id', 'slug')->toArray();

        $force = (bool) $this->option('force');
        $clearOld = (bool) $this->option('clear-old');

        $this->info('Starting sync of users (chunked)');

        $updated = 0;
        $skipped = 0;
        $createdRoles = 0;

        DB::table('users')->orderBy('id')->chunkById(200, function ($users) use (&$updated, &$skipped, &$roles, &$createdRoles, $force) {
            foreach ($users as $user) {
                $slug = trim((string) ($user->role ?? ''));

                if ($slug === '') {
                    $skipped++;
                    continue;
                }

                if (! isset($roles[$slug])) {
                    $this->line("Creating missing role: {$slug}");
                    $name = str($slug)->replace('_', ' ')->title()->toString();
                    $id = DB::table('roles')->insertGetId([
                        'name' => $name,
                        'slug' => $slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $roles[$slug] = $id;
                    $createdRoles++;
                }

                $roleId = $roles[$slug];

                if (! $force && $user->role_id) {
                    $skipped++;
                    continue;
                }

                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
                $updated++;
            }
        });

        if ($clearOld) {
            $this->info('Clearing old `role` column values...');
            try {
                DB::table('users')->whereNotNull('role')->update(['role' => null]);
            } catch (\Illuminate\Database\QueryException $e) {
                $this->warn('Could not set NULL due to DB constraint, setting empty string instead.');
                DB::table('users')->whereNotNull('role')->update(['role' => '']);
            }
        }

        $this->info("Sync complete. Updated: {$updated}, Skipped(blank or already set): {$skipped}, Roles created: {$createdRoles}");

        return self::SUCCESS;
    }
}
