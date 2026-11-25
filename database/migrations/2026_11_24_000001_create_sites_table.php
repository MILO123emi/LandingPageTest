<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('path', 40)->unique();
            $table->string('name', 120);
            $table->string('domain')->nullable()->unique();
            $table->json('alt_domains')->nullable();
            $table->string('status', 10)->default('draft');
            $table->string('locale', 10)->default('es_HN');
            $table->string('timezone', 40)->default('America/Tegucigalpa');
            $table->json('settings')->nullable();
            $table->json('draft_settings')->nullable();
            $table->json('theme')->nullable();
            $table->json('draft_theme')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
