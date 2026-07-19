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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('helpful_rating')->nullable();
            $table->string('is_personal')->nullable();
            $table->string('understood_level')->nullable();
            $table->string('would_use_again')->nullable();
            $table->string('use_frequency')->nullable();
            $table->text('most_valuable')->nullable();
            $table->text('confused_or_disappointed')->nullable();
            $table->text('feature_to_come_back')->nullable();
            $table->string('monthly_price_willingness')->nullable();
            $table->text('subscription_convincer')->nullable();
            $table->unsignedTinyInteger('nps_score')->nullable();
            $table->text('one_thing_to_change')->nullable();
            $table->boolean('is_skipped')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
