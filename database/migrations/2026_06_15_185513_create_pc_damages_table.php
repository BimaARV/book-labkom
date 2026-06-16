<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pc_damages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_pc_id')->constrained('lab_pcs')->onDelete('cascade');
            $table->text('description');
            $table->enum('status', ['reported', 'fixing', 'fixed'])->default('reported');
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pc_damages');
    }
};
