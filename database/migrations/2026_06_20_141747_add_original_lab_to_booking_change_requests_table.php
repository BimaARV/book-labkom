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
        Schema::table('booking_change_requests', function (Blueprint $table) {
            $table->foreignId('original_laboratory_id')->nullable()->constrained('laboratories')->onDelete('set null');
            $table->boolean('original_is_all_labs')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_change_requests', function (Blueprint $table) {
            $table->dropForeign(['original_laboratory_id']);
            $table->dropColumn(['original_laboratory_id', 'original_is_all_labs']);
        });
    }
};
