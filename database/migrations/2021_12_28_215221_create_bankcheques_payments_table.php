<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBankchequesPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bankcheques_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('payment_id');
            $table->integer('transaction_id');
            $table->integer('business_id');
            $table->string('cheque_number');
            $table->string('cheque_ref');
            $table->string('cheque_date');
            $table->integer('amount');
            $table->string('transaction_type');
            $table->string('comment');
            $table->integer('userid');
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
        Schema::dropIfExists('bankcheques_payments');
    }
}
