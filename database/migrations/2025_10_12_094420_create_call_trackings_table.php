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
        Schema::create('call_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone_number');
            $table->timestamp('call_started_at')->nullable();
            $table->timestamp('call_ended_at')->nullable();
            $table->integer('call_duration_seconds')->default(0);
            $table->enum('call_status', ['initiated', 'ringing', 'answered', 'completed', 'cancelled', 'failed', 'busy', 'no_answer'])->default('initiated');
            $table->text('call_summary')->nullable();
            $table->string('audio_recording_path')->nullable();
            $table->string('call_id')->unique()->nullable(); // For tracking calls across devices
            $table->json('call_metadata')->nullable(); // Store additional call data
            $table->string('device_type')->default('web'); // web, android, ios
            $table->string('device_id')->nullable(); // For mobile app tracking
            $table->boolean('is_auto_dialed')->default(false);
            $table->timestamps();

            $table->index(['lead_id', 'user_id']);
            $table->index(['call_started_at']);
            $table->index(['call_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_trackings');
    }
};
