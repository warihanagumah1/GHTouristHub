<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tourist_regions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('overview')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'name']);
        });

        Schema::create('tourist_attractions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tourist_region_id')->constrained('tourist_regions')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['tourist_region_id', 'is_published']);
            $table->index(['tourist_region_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tourist_attractions');
        Schema::dropIfExists('tourist_regions');
    }
};
