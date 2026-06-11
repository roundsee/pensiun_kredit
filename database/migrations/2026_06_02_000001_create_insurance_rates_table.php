<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_rates', function (Blueprint $table) {
            $table->id();
            $table->string('product')->index();
            $table->integer('tenor')->index();
            // premium per 1,000,000 of plafond (currency units)
            $table->decimal('premium_per_million', 12, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_rates');
    }
};
