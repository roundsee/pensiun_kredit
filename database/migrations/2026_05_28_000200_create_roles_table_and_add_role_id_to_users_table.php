<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        $now = now();

        DB::table('roles')->upsert([
            ['name' => 'Marketing', 'slug' => 'marketing', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Support Bisnis', 'slug' => 'support_bisnis', 'created_at' => $now, 'updated_at' => $now],
        ], ['slug'], ['name', 'updated_at']);

        if (! Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $column = $table->foreignId('role_id')->nullable();

                if (Schema::hasColumn('users', 'role')) {
                    $column->after('role');
                } else {
                    $column->after('email');
                }

                $column->constrained('roles')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('users', 'role')) {
            $roleMap = DB::table('roles')->pluck('id', 'slug');

            foreach ($roleMap as $slug => $id) {
                DB::table('users')
                    ->where('role', $slug)
                    ->update(['role_id' => $id]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('role_id');
            });
        }

        Schema::dropIfExists('roles');
    }
};