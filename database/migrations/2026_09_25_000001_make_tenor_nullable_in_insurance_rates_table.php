<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_rates', function (Blueprint $table): void {
            $table->integer('tenor')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('insurance_rates')->whereNull('tenor')->exists()) {
            throw new RuntimeException('Cannot restore insurance_rates.tenor while age-only rates exist.');
        }

        Schema::table('insurance_rates', function (Blueprint $table): void {
            $table->integer('tenor')->nullable(false)->change();
        });
    }
};
