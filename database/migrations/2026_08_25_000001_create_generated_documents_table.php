<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_documents');
    }
};
