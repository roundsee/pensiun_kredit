<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            $table->string('permohonan_cif_file')->nullable();
            $table->string('pelunasan_to_kb_file')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('data_simulasi_pelengkap', function (Blueprint $table) {
            $table->dropColumn('permohonan_cif_file');
            $table->dropColumn('pelunasan_to_kb_file');
        });
    }
};
