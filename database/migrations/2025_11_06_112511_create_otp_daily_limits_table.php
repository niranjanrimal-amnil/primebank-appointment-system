<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('otp_daily_limits', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->index();
            $table->date('date');
            $table->integer('send_count')->default(0);
            $table->integer('resend_count')->default(0);
            $table->timestamps();
            
            $table->unique(['account_number', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('otp_daily_limits');
    }
};