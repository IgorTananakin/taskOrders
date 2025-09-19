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
            $table->string('full_name'); // ФИО
            $table->string('phone'); // телефон
            $table->string('email'); // почта
            $table->string('inn'); // ИНН
            $table->string('company_name'); // Название компании
            $table->text('address'); // Адрес
            $table->enum('status', ['new', 'in_progress', 'completed', 'cancelled'])->default('new'); // статус
            $table->date('order_date'); // дата заказа
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};