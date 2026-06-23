<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('insurance_rates', function (Blueprint $table) {
            if (!Schema::hasColumn('insurance_rates', 'bank_tujuan')) {
                $table->string('bank_tujuan')->nullable()->after('tenor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insurance_rates', function (Blueprint $table) {
            if (Schema::hasColumn('insurance_rates', 'bank_tujuan')) {
                $table->dropColumn('bank_tujuan');
            }
        });
    }
};
