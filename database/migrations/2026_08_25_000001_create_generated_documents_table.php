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
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id', 'document_type']);
            $table->index(['generated_by']);
            $table->foreign('generated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_documents');
    }
};
