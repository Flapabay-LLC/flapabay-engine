<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_options', function (Blueprint $table) {
            $table->bigIncrements('id'); // Auto-increment primary key
            $table->unsignedBigInteger('user_id'); // Foreign key for the user
            $table->string('payment_method')->nullable(); // Payment method (e.g., credit_card, bank_transfer, paypal)
            $table->string('account_number')->nullable(); // Account number or payment identifier (e.g., card number or PayPal account)
            $table->string('expiration_date')->nullable(); // Expiration date (MM/YY for cards)
            $table->string('country_code')->nullable(); // Country code (optional, e.g., US, IN)
            $table->string('currency')->nullable(); // Currency type (optional, e.g., USD, EUR)
            $table->timestamps(); // Created at & Updated at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_options');
    }
}
