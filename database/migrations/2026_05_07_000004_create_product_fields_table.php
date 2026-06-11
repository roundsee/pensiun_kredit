<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('field_name');
            $table->string('field_label');
            $table->string('field_type');
            $table->boolean('is_required')->default(true);
            $table->enum('group', ['informasi_debitur', 'data_pengajuan']);
            $table->timestamps();

            $table->unique(['product_id', 'field_name', 'group'], 'product_fields_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_fields');
    }
};
