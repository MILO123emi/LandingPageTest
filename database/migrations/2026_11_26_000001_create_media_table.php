<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 20)->default('public');
            $table->string('path_original');
            $table->json('variants')->nullable();
            $table->text('lqip')->nullable();
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('mime', 40);
            $table->unsignedInteger('size_bytes');
            $table->decimal('focal_x', 4, 3)->default(0.5);
            $table->decimal('focal_y', 4, 3)->default(0.5);
            $table->string('alt', 200)->default('');
            $table->json('palette')->nullable();
            $table->timestamps();

            $table->index('site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
