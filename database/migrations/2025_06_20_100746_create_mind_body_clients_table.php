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
        Schema::create('mind_body_clients', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_gender_preference')->nullable();
            $table->dateTime('birth_date')->nullable();
            $table->string('country')->nullable();
            $table->dateTime('creation_date')->nullable();
            $table->json('custom_client_fields')->nullable();
            $table->json('client_credit_card')->nullable();

            $table->dateTime('first_appointment_date')->nullable();
            $table->dateTime('first_class_date')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('mindbody_client_id')->nullable();
            $table->unsignedBigInteger('unique_id')->nullable();
            $table->boolean('is_company')->nullable();
            $table->boolean('is_prospect')->nullable();

            $table->boolean('liability_release')->nullable();
            $table->integer('membership_icon')->nullable();
            $table->integer('mobile_provider')->nullable();
            $table->text('notes')->nullable();
            $table->string('state')->nullable();
            $table->dateTime('last_modified_date_time')->nullable();
            $table->string('red_alert')->nullable();
            $table->string('yellow_alert')->nullable();
            $table->string('prospect_stage')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('home_phone')->nullable();
            $table->string('work_phone')->nullable();
            $table->decimal('account_balance', 10, 2)->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('work_extension')->nullable();
            $table->string('referred_by')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('emergency_contact_info_name')->nullable();
            $table->string('emergency_contact_info_email')->nullable();
            $table->string('emergency_contact_info_phone')->nullable();
            $table->string('emergency_contact_info_relationship')->nullable();
            $table->string('gender')->nullable();
            $table->text('last_formula_notes')->nullable();
            $table->boolean('active')->nullable();
            $table->boolean('send_account_emails')->nullable();
            $table->boolean('send_account_texts')->nullable();
            $table->boolean('send_promotional_emails')->nullable();
            $table->boolean('send_promotional_texts')->nullable();
            $table->boolean('send_schedule_emails')->nullable();
            $table->boolean('send_schedule_texts')->nullable();
            $table->string('status')->nullable();
            $table->string('action')->nullable();
            $table->string('locker_number')->nullable();

            $table->json('sales_reps')->nullable();              // JSON 1
            $table->json('home_location')->nullable();           // JSON 2
            $table->json('suspension_info')->nullable();
            $table->json('client_indexes')->nullable();
            $table->json('client_relationships')->nullable();    // JSON 3
            $table->json('liability')->nullable();               // JSON 4
            $table->json('json_data')->nullable();               // JSON 5
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mind_body_clients');
    }
};
