<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('mind_body_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mindbody_session_id')->unique();
            $table->string('type')->nullable();
            $table->integer('default_time_length')->nullable();
            $table->integer('staff_time_length')->nullable();
            $table->string('name')->nullable();
            $table->text('online_description')->nullable();
            $table->integer('num_deducted')->nullable();
            $table->unsignedBigInteger('program_id')->nullable();
            $table->string('category')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('subcategory')->nullable();
            $table->unsignedBigInteger('subcategory_id')->nullable();
            $table->boolean('available_for_add_on')->default(false);
            $table->json('json_data')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('mind_body_sessions');
    }
};
