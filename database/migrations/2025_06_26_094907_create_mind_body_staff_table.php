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
        Schema::create('mind_body_staff', function (Blueprint $table) {
            $table->id();
            $table->BigInteger('mindbody_staff_id')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('home_phone')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('image_url')->nullable();
            $table->text('bio')->nullable();

            $table->boolean('appointment_instructor')->nullable();
            $table->boolean('always_allow_double_booking')->nullable();
            $table->boolean('independent_contractor')->nullable();
            $table->boolean('is_male')->nullable();
            $table->boolean('class_teacher')->nullable();
            $table->boolean('class_assistant')->nullable();
            $table->boolean('class_assistant2')->nullable();

            $table->boolean('rep')->nullable();
            $table->boolean('rep2')->nullable();
            $table->boolean('rep3')->nullable();
            $table->boolean('rep4')->nullable();
            $table->boolean('rep5')->nullable();
            $table->boolean('rep6')->nullable();

            $table->integer('sort_order')->nullable();
            $table->string('emp_id')->nullable();

            $table->timestamp('employment_start')->nullable();
            $table->timestamp('employment_end')->nullable();

            $table->json('provider_ids')->nullable();
            $table->json('staff_settings')->nullable();
            $table->json('appointments')->nullable();
            $table->json('unavailabilities')->nullable();
            $table->json('availabilities')->nullable();

            $table->json('json_data')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mind_body_staff');
    }
};
