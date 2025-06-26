<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('appointo_appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id')->unique(); // external ID from API
            $table->boolean('activate')->nullable();
            $table->string('product_uuid'); // Shopify Product GID
            $table->string('duration_uuid'); // Shopify ProductVariant GID
            $table->unsignedBigInteger('product_detail_id');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('currency', 10);

            // JSON columns
            $table->json('appointment_config')->nullable();
            $table->json('team_members')->nullable();
            $table->json('groups')->nullable();
            $table->json('weekly_availabilities')->nullable();
            $table->json('overridden_availabilities')->nullable();
            $table->json('custom_fields')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('appointo_appointments');
    }
};
