<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->uuid('purpose_id');
            $table->string('purpose_name');
            $table->text('api_key'); // Encrypted
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('purpose_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_keys');
    }
};