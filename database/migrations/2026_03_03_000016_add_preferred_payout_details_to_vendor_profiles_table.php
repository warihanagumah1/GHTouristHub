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
        Schema::table('vendor_profiles', function (Blueprint $table): void {
            $table->text('preferred_payout_details')->nullable()->after('payout_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table): void {
            $table->dropColumn('preferred_payout_details');
        });
    }
};
