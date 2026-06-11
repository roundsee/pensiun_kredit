<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('template_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_template_id')->constrained('product_templates')->onDelete('cascade');
            $table->foreignId('account_group_id')->constrained('account_groups');
            $table->string('coa_code');
            $table->string('name');
            $table->decimal('percentage', 5, 2)->nullable();
            $table->enum('formula_type', ['percentage', 'fixed', 'custom']);
            $table->decimal('min_value', 15, 2)->nullable();
            $table->decimal('max_value', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_accounts');
    }
};
