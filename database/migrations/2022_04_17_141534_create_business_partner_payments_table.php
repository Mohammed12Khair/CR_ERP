<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessPartnerPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_partner_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id')->unsigned();
            $table->integer('amount')->unsigned();
            $table->integer('business_transaction')->unsigned();
            $table->integer('transaction_id')->unsigned();
            $table->integer('payment_id')->unsigned();
            $table->integer('account')->unsigned();
            $table->string('note');
            $table->integer('owner')->unsigned();
            $table->integer('is_active')->default(0);
            $table->integer('created_by')->unsigned();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_partner_payments');
    }
}
