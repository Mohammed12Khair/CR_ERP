<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccounttransactionsClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounttransactions_clones', function (Blueprint $table) {
            $table->unsignedInteger('id')->default('0');
            $table->integer('account_id');
            $table->enum('type', ['debit', 'credit']);
            $table->enum('sub_type', ['opening_balance', 'fund_transfer', 'deposit'])->nullable();
            $table->decimal('amount', 22, 4);
            $table->string('reff_no', 191)->nullable();
            $table->dateTime('operation_date');
            $table->integer('created_by');
            $table->integer('transaction_id')->nullable();
            $table->integer('transaction_payment_id')->nullable();
            $table->integer('transfer_transaction_id')->nullable();
            $table->text('note')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('accounttransactions_clones');
    }
}
