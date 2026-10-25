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
            if (!Schema::hasColumn('users', 'is_pro')) {
                $table->boolean('is_pro')->default(false)->after('is_admin');
            }
            if (!Schema::hasColumn('users', 'pro_expires_at')) {
                $table->timestamp('pro_expires_at')->nullable()->after('is_pro');
            }
            if (!Schema::hasColumn('users', 'subscription_status')) {
                $table->string('subscription_status')->default('free')->after('pro_expires_at');
            }
            if (!Schema::hasColumn('users', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
            }
            if (!Schema::hasColumn('users', 'has_used_trial')) {
                $table->boolean('has_used_trial')->default(false)->after('trial_ends_at');
            }
            if (!Schema::hasColumn('users', 'razorpay_subscription_id')) {
                $table->string('razorpay_subscription_id')->nullable()->after('has_used_trial');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $dropCols = [];
            foreach (['is_pro', 'pro_expires_at', 'subscription_status', 'trial_ends_at', 'has_used_trial', 'razorpay_subscription_id'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $dropCols[] = $col;
                }
            }
            if (!empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });
    }
};
