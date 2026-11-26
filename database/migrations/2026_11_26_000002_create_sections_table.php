<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->unsignedSmallInteger('position');
            $table->boolean('is_visible')->default(true);
            $table->json('content');
            $table->json('draft_content')->nullable();
            $table->unsignedSmallInteger('draft_position')->nullable();
            $table->boolean('draft_is_visible')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
