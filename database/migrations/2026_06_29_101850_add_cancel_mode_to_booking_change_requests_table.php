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
            $table->string('cancel_mode')->nullable()->after('type'); // 'all' or 'partial'
            $table->date('cancel_from_date')->nullable()->after('cancel_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_change_requests', function (Blueprint $table) {
            $table->dropColumn(['cancel_mode', 'cancel_from_date']);
        });
    }
};
