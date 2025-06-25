<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('mindbody_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained('users')->onDelete('cascade'); // Corrected
            $table->foreignId('mindbody_client_id')->nullable()->constrained('mindbody_clients')->onDelete('cascade');
            $table->string('mindbody_appointment_id')->nullable();
            $table->unsignedBigInteger('appointment_type_id')->nullable();
            $table->unsignedBigInteger('session_type_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('mindbody_appointments');
    }
};
