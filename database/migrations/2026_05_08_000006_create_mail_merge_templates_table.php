<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mail_merge_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('document_type', 50);
            $table->string('source_pdf_path')->nullable();
            $table->string('generated_view_path')->nullable();
            $table->longText('template_html')->nullable();
            $table->json('slot_definitions')->nullable();
            $table->json('mappings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_merge_templates');
    }
};