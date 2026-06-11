<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_id')->constrained('product_templates')->onDelete('cascade');
            $table->string('field_name');          // snake_case identifier
            $table->string('field_label');          // display label (matches PDF label)
            $table->string('field_type')->default('text'); // text, number, date, dropdown
            $table->boolean('is_required')->default(false);
            $table->enum('section', ['informasi_debitur', 'data_pengajuan', 'data_financial']);
            $table->string('account_code')->nullable();     // COA code for financial items
            $table->enum('calculation_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('default_value', 15, 4)->nullable();
            $table->unsignedSmallInteger('field_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_fields');
    }
};
