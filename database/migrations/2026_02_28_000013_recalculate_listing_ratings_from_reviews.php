<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('listings')->update([
            'rating_average' => 0,
            'rating_count' => 0,
        ]);

        $stats = DB::table('tenant_reviews')
            ->selectRaw('listing_id, COUNT(*) as reviews_count, AVG(rating) as average_rating')
            ->groupBy('listing_id')
            ->get();

        foreach ($stats as $row) {
            DB::table('listings')
                ->where('id', $row->listing_id)
                ->update([
                    'rating_count' => (int) $row->reviews_count,
                    'rating_average' => round((float) $row->average_rating, 2),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible: previous dummy values are intentionally discarded.
    }
};
