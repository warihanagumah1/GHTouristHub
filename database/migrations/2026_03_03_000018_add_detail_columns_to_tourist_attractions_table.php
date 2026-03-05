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
        Schema::table('tourist_attractions', function (Blueprint $table): void {
            $table->string('hero_image_url')->nullable()->after('description');
            $table->json('gallery_images')->nullable()->after('hero_image_url');
            $table->string('address')->nullable()->after('city');
            $table->string('visiting_hours')->nullable()->after('address');
            $table->string('entry_fee')->nullable()->after('visiting_hours');
            $table->string('best_time_to_visit')->nullable()->after('entry_fee');
            $table->string('contact_info')->nullable()->after('best_time_to_visit');
            $table->string('website_url')->nullable()->after('contact_info');
            $table->text('how_to_get_there')->nullable()->after('website_url');
            $table->text('travel_tips')->nullable()->after('how_to_get_there');
            $table->text('safety_notes')->nullable()->after('travel_tips');
            $table->json('featured_activities')->nullable()->after('safety_notes');
            $table->json('nearby_places')->nullable()->after('featured_activities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tourist_attractions', function (Blueprint $table): void {
            $table->dropColumn([
                'hero_image_url',
                'gallery_images',
                'address',
                'visiting_hours',
                'entry_fee',
                'best_time_to_visit',
                'contact_info',
                'website_url',
                'how_to_get_there',
                'travel_tips',
                'safety_notes',
                'featured_activities',
                'nearby_places',
            ]);
        });
    }
};
