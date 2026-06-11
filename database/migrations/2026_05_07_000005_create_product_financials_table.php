<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_financials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('item_name');
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->enum('calculation_type', ['percentage', 'fixed']);
            $table->decimal('default_value', 15, 2);
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->boolean('is_deducted_at_disbursement')->default(true);
            $table->boolean('is_included_in_simulation')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'item_name'], 'product_financials_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_financials');
    }
};
