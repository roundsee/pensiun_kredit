<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number')->unique();
            $table->foreignId('nasabah_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('lender_id')->constrained('lenders')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('amount_plafond', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('provision_fee', 15, 2)->default(0);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->date('disbursed_at')->nullable();
            $table->timestamps();

            $table->index(['lender_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
