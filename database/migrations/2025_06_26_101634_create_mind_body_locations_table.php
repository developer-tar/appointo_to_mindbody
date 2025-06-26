<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('mind_body_locations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('mindbody_location_id')->unique();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state_prov_code')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_extension')->nullable();
            $table->text('business_description')->nullable();
            $table->text('description')->nullable();
            $table->boolean('has_classes')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->float('tax1')->nullable();
            $table->float('tax2')->nullable();
            $table->float('tax3')->nullable();
            $table->float('tax4')->nullable();
            $table->float('tax5')->nullable();
            $table->integer('total_number_of_ratings')->nullable();
            $table->float('average_rating')->nullable();
            $table->integer('total_number_of_deals')->nullable();
            $table->json('additional_image_urls')->nullable();
            $table->json('amenities')->nullable();
            $table->json('json_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('mind_body_locations');
    }
};
