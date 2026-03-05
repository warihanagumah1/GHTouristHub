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
        Schema::table('bookings', function (Blueprint $table): void {
            $table->date('service_date')->nullable()->after('special_requests');
            $table->timestamp('pending_payment_reminded_at')->nullable()->after('paid_at');
            $table->timestamp('upcoming_service_reminded_at')->nullable()->after('pending_payment_reminded_at');

            $table->index(['status', 'service_date']);
            $table->index(['status', 'pending_payment_reminded_at']);
            $table->index(['status', 'upcoming_service_reminded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex(['status', 'service_date']);
            $table->dropIndex(['status', 'pending_payment_reminded_at']);
            $table->dropIndex(['status', 'upcoming_service_reminded_at']);
            $table->dropColumn([
                'service_date',
                'pending_payment_reminded_at',
                'upcoming_service_reminded_at',
            ]);
        });
    }
};
