<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'cancellation', 'reschedule', 'relocation'
            $table->date('requested_date')->nullable();
            $table->time('requested_start_time')->nullable();
            $table->time('requested_end_time')->nullable();
            $table->foreignId('requested_laboratory_id')->nullable()->constrained('laboratories')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_change_requests');
    }
};
