<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('otp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->index();
            $table->string('otp_code');
            $table->enum('send_type', ['email', 'sms', 'both'])->default('both');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at');
            $table->integer('attempt_count')->default(0);
            $table->enum('status', ['active', 'expired', 'verified', 'failed'])->default('active');
            $table->timestamps();
            
            $table->index(['account_number', 'status']);
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('otp_logs');
    }
};