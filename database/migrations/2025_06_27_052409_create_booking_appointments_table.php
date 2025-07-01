<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('booking_appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_id')->nullable();
            $table->tinyInteger('source')->nullable()->comment('1=Appointo, 2=MindBody');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('timestring')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_sync')->nullable();
            $table->string('client_id')->nullable();
            $table->string('location_id')->nullable();
            $table->string('session_type_id')->nullable();
            $table->string('staff_id')->nullable();
           
            $table->json('source_json_data')->nullable();
            $table->json('after_sync_json_data')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('booking_appointments');
    }
};
