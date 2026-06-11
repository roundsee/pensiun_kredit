<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pic_nbp') && !Schema::hasTable('idpb')) {
            Schema::rename('pic_nbp', 'idpb');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('idpb') && !Schema::hasTable('pic_nbp')) {
            Schema::rename('idpb', 'pic_nbp');
        }
    }
};
