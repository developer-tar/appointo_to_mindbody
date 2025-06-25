<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('appointo_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('appointment_id')->nullable();
            $table->string('timestring')->nullable();
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('appointo_booking_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('appointo_bookings');
    }
};
