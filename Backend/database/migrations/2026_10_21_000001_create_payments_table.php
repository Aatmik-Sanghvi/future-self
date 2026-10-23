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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('razorpay_order_id')->index();
            $table->string('razorpay_payment_id')->nullable()->index();
            $table->string('razorpay_signature')->nullable();
            $table->unsignedInteger('amount'); // in paise (e.g. 19900 = ₹199)
            $table->string('currency', 10)->default('INR');
            $table->string('status', 20)->default('created'); // created, paid, failed
            $table->string('plan', 50)->default('pro_monthly');
            $table->string('razorpay_subscription_id')->nullable()->after('razorpay_order_id')->index();
            $table->string('razorpay_order_id')->nullable()->change();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
