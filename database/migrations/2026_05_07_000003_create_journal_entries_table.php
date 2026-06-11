<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->foreignId('nasabah_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained('loans')->nullOnDelete();
            $table->foreignId('lender_id')->nullable()->constrained('lenders')->nullOnDelete();
            $table->string('reference')->nullable();
            $table->enum('posting_status', ['posted', 'reversed'])->default('posted');
            $table->foreignId('reversed_from_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->string('void_reason')->nullable();
            $table->string('description');
            $table->timestamps();

            $table->index(['transaction_date', 'account_id']);
            $table->index(['lender_id', 'transaction_date']);
            $table->index(['loan_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
