<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->index();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->uuid('purpose_id');
            $table->string('purpose_name');
            $table->uuid('location_id');
            $table->string('location_name');
            $table->uuid('assigned_staff_id')->nullable();
            $table->string('staff_name')->nullable();
            $table->dateTime('proposed_date_time');
            $table->dateTime('scheduled_date_time');
            $table->text('remarks')->nullable();
            $table->text('appointment_metadata')->nullable();
            $table->string('customer_timezone');
            $table->string('agent_timezone')->nullable();
            $table->dateTime('appointment_taken_at')->nullable();
            $table->dateTime('appointment_confirmed_at')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
            
            $table->index(['account_number', 'status']);
            $table->index('scheduled_date_time');
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};