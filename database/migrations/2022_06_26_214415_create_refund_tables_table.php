<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_tables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('transaction_id')->nullable();
            $table->unsignedInteger('contact_id')->nullable();
            $table->integer('business_id')->unsigned();
            $table->decimal('refund_total')->nullable();
            $table->integer('status')->default(0);
            $table->integer('is_active')->default(0);
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
        Schema::dropIfExists('refund_tables');
    }
}
