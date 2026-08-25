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
        Schema::create('daily_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('goal_id')->nullable()->constrained('goals')->onDelete('set null');
            $table->date('mission_date')->index();
            $table->string('title');
            $table->text('description');
            $table->text('future_self_note')->nullable();
            $table->string('category')->nullable();
            $table->string('mood_type')->nullable();
            $table->unsignedSmallInteger('estimated_minutes')->default(15);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->enum('status', ['pending', 'completed', 'skipped'])->default('pending')->index();
            $table->timestamp('completed_at')->nullable();
            $table->text('reflection')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'mission_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_missions');
    }
};
