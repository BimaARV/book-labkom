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
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('is_all_labs')->default(false)->after('laboratory_id');
        });
        
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE bookings MODIFY laboratory_id BIGINT UNSIGNED NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('is_all_labs');
        });
        
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE bookings MODIFY laboratory_id BIGINT UNSIGNED NOT NULL;');
    }
};
