<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('report_image');
            $table->json('report_images')->nullable()->after('status');
            $table->boolean('is_clean')->default(false)->after('report_note');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('report_image')->nullable()->after('status');
            $table->dropColumn(['report_images', 'is_clean']);
        });
    }
};
