<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('appointo_mindbody_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('appointo_booking_id')->nullable()->constrained('appointo_bookings')->onDelete('cascade');
            $table->foreignId('mindbody_appointment_id')->nullable()->constrained('mindbody_appointments')->onDelete('cascade');
            $table->tinyInteger('source')->default(config('constants.type.appointo'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('appointo_mindbody_syncs');
    }
};
