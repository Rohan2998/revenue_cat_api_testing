<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_table', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->string('current_payment_id')->nullable();
            $table->string('subscription_type')->nullable();
            $table->timestamp('subscription_Start_date');
            $table->timestamp('subscription_end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_table');
    }
}
