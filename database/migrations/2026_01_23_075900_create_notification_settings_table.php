<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title'); 
            $table->text('description')->nullable();

            $table->nullableMorphs('target'); 

            $table->integer('recurrence_days')->nullable(); 
            $table->integer('reminder_days')->nullable();   
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down() {
        
        Schema::dropIfExists('notification_settings');
    }
};
