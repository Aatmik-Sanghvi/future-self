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
        Schema::table('goals', function (Blueprint $table) {
            $table->decimal('target_value', 12, 2)->nullable()->after('priority');
            $table->decimal('current_value', 12, 2)->nullable()->default(0)->after('target_value');
            $table->string('unit', 50)->nullable()->after('current_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['target_value', 'current_value', 'unit']);
        });
    }
};
