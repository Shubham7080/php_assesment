<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('snapshot');
            $table->timestamps();

            $table->unique(['book_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_versions');
    }
};
