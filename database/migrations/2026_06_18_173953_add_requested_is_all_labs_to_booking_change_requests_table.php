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
            $table->boolean('requested_is_all_labs')->default(false)->after('requested_laboratory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_change_requests', function (Blueprint $table) {
            $table->dropColumn('requested_is_all_labs');
        });
    }
};
