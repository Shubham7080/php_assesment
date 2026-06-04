<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chapter_id')->constrained('chapters')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->index(['chapter_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
