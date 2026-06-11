<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('simulation_fields', function (Blueprint $table) {
            $table->id();
            $table->uuid('simulation_batch_id')->index();
            $table->string('source_filename')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained('loans')->nullOnDelete();
            $table->string('field_key');
            $table->string('field_label');
            $table->text('field_value')->nullable();
            $table->string('section')->nullable();
            $table->unsignedInteger('line_order')->default(0);
            $table->text('raw_line')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->timestamps();

            $table->index(['simulation_batch_id', 'line_order']);
            $table->index(['field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulation_fields');
    }
};
