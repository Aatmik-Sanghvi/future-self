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
        Schema::create('struggles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->references('id')->on('goals')->cascadeOnDelete();
            $table->text('struggle');
            $table->string('category')->nullable();
            $table->tinyInteger('severity')->default(3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('struggles');
    }
};
