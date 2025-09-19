<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('phone'); 
            $table->string('email'); 
            $table->string('inn'); 
            $table->string('company_name'); 
            $table->text('address'); 
            $table->enum('status', ['new', 'in_progress', 'completed', 'cancelled'])->default('new'); 
            $table->date('order_date'); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};