<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_pcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_id')->constrained('laboratories')->onDelete('cascade');
            $table->string('name');
            $table->string('ip_address')->nullable();
            $table->integer('grid_row');
            $table->integer('grid_col');
            $table->enum('status', ['active', 'maintenance', 'broken'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_pcs');
    }
};
