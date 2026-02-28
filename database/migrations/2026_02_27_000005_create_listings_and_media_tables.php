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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->enum('type', ['tour', 'utility']);
            $table->string('subtype')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('price_from', 12, 2)->default(0);
            $table->string('pricing_unit')->nullable();
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->json('highlights')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->json('amenities')->nullable();
            $table->json('languages')->nullable();
            $table->json('itinerary')->nullable();
            $table->string('duration_label')->nullable();
            $table->string('group_size_label')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->text('booking_rules')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft', 'pending_review', 'published', 'paused', 'blocked'])->default('draft');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['country', 'city']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'deleted_at']);
        });

        Schema::create('listing_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('url');
            $table->string('thumbnail_url')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();

            $table->index(['listing_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_media');
        Schema::dropIfExists('listings');
    }
};
