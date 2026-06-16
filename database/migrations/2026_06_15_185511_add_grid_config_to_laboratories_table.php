<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratories', function (Blueprint $table) {
            $table->integer('grid_rows')->nullable()->after('status');
            $table->integer('grid_cols')->nullable()->after('grid_rows');
            $table->enum('grid_direction', ['ltr', 'rtl'])->default('ltr')->after('grid_cols');
        });
    }

    public function down(): void
    {
        Schema::table('laboratories', function (Blueprint $table) {
            $table->dropColumn(['grid_rows', 'grid_cols', 'grid_direction']);
        });
    }
};
