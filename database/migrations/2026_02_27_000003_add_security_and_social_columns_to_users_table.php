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
        Schema::table('users', function (Blueprint $table) {
            $table->string('social_provider')->nullable()->after('remember_token');
            $table->string('social_provider_id')->nullable()->after('social_provider');
            $table->string('avatar_url')->nullable()->after('social_provider_id');
            $table->boolean('two_factor_enabled')->default(false)->after('avatar_url');
            $table->string('two_factor_code')->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code');

            $table->index(['social_provider', 'social_provider_id']);
            $table->index('two_factor_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['social_provider', 'social_provider_id']);
            $table->dropIndex(['two_factor_enabled']);
            $table->dropColumn([
                'social_provider',
                'social_provider_id',
                'avatar_url',
                'two_factor_enabled',
                'two_factor_code',
                'two_factor_expires_at',
            ]);
        });
    }
};
