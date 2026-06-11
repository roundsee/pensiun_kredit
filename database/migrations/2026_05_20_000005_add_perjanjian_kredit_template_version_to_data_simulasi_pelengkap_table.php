<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            if (!Schema::hasColumn('data_simulasi_pelengkap', 'perjanjian_kredit_template_version')) {
                $table->enum('perjanjian_kredit_template_version', ['standard', 'kb'])->default('standard')->after('no_pk')->comment('PK template version: standard or kb');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            if (Schema::hasColumn('data_simulasi_pelengkap', 'perjanjian_kredit_template_version')) {
                $table->dropColumn('perjanjian_kredit_template_version');
            }
        });
    }
};
