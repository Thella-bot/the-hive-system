<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_books', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->integer('publish_year')->nullable();
            $table->string('edition')->nullable();
            $table->text('description')->nullable();
            $table->uuid('category_id')->nullable();
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('location')->nullable();
            $table->string('call_number')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('book_categories')->onDelete('set null');
            $table->index(['title', 'author']);
            $table->index('isbn');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};