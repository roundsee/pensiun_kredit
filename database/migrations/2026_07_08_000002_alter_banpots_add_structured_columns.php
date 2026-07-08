<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('banpots', function (Blueprint $table) {
            if (!Schema::hasColumn('banpots', 'no')) {
                $table->string('no')->nullable()->after('row_number');
                $table->string('no_pk')->nullable()->after('no');
                $table->string('nopen')->nullable()->after('no_pk');
                $table->string('norek')->nullable()->after('nopen');
                $table->string('total')->nullable()->after('norek');
                $table->string('cabang')->nullable()->after('total');
                $table->string('status')->nullable()->after('cabang');
                $table->string('plafond')->nullable()->after('status');
                $table->string('selisih')->nullable()->after('plafond');
                $table->string('giro_mitra')->nullable()->after('selisih');
                $table->string('keterangan')->nullable()->after('giro_mitra');
                $table->string('customer_id')->nullable()->after('keterangan');
                $table->string('nama_debitur')->nullable()->after('customer_id');
                $table->string('jenis_tagihan')->nullable()->after('nama_debitur');
                $table->string('pengelola_pensiun')->nullable()->after('jenis_tagihan');
                $table->string('pendebetan_angsuran')->nullable()->after('pengelola_pensiun');
                $table->string('nama_mitra_channeling')->nullable()->after('pendebetan_angsuran');
            }

            if (!Schema::hasColumn('banpots', 'row_data')) {
                $table->json('row_data')->nullable()->after('nama_mitra_channeling');
            }
        });
    }

    public function down(): void
    {
        Schema::table('banpots', function (Blueprint $table) {
            $table->dropColumn([
                'no',
                'no_pk',
                'nopen',
                'norek',
                'total',
                'cabang',
                'status',
                'plafond',
                'selisih',
                'giro_mitra',
                'keterangan',
                'customer_id',
                'nama_debitur',
                'jenis_tagihan',
                'pengelola_pensiun',
                'pendebetan_angsuran',
                'nama_mitra_channeling',
            ]);
        });
    }
};