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
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('support_email');
            $table->string('banner_url')->nullable()->after('logo_url');
            $table->string('website_url')->nullable()->after('banner_url');
            $table->unsignedSmallInteger('founded_year')->nullable()->after('website_url');
            $table->text('about')->nullable()->after('founded_year');
            $table->string('stripe_connect_account_id')->nullable()->after('about');
            $table->enum('payout_mode', ['platform_payouts', 'connect_destination'])->default('platform_payouts')->after('stripe_connect_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'logo_url',
                'banner_url',
                'website_url',
                'founded_year',
                'about',
                'stripe_connect_account_id',
                'payout_mode',
            ]);
        });
    }
};
