<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_merge_templates', function (Blueprint $table) {
            // When set, this blade view (e.g. sppk.template) is used instead of the generated one
            $table->string('existing_blade_view')->nullable()->after('generated_view_path');
        });
    }

    public function down(): void
    {
        Schema::table('mail_merge_templates', function (Blueprint $table) {
            $table->dropColumn('existing_blade_view');
        });
    }
};
