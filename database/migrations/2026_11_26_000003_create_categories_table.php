<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('slug', 90);
            $table->string('description', 300)->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
            $table->index(['site_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
