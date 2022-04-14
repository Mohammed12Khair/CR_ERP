<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionSellLinesClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_sell_lines_clones', function (Blueprint $table) {
            $table->unsignedInteger('id')->default('0');
            $table->unsignedInteger('transaction_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variation_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('quantity_returned', 20, 4)->default(0);
            $table->decimal('unit_price_before_discount', 22, 4)->default(0);
            $table->decimal('unit_price', 22, 4)->nullable()->comment('Sell price excluding tax');
            $table->enum('line_discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('line_discount_amount', 22, 4)->default(0);
            $table->decimal('unit_price_inc_tax', 22, 4)->nullable()->comment('Sell price including tax');
            $table->decimal('item_tax', 22, 4)->comment('Tax for one quantity');
            $table->unsignedInteger('tax_id')->nullable();
            $table->integer('discount_id')->nullable();
            $table->integer('lot_no_line_id')->nullable();
            $table->text('sell_line_note')->nullable();
            $table->integer('so_line_id')->nullable();
            $table->decimal('so_quantity_invoiced', 22, 4)->default(0);
            $table->integer('res_service_staff_id')->nullable();
            $table->string('res_line_order_status', 191)->nullable();
            $table->integer('parent_sell_line_id')->nullable();
            $table->string('children_type', 191)->default('')->comment('Type of children for the parent, like modifier or combo');
            $table->integer('sub_unit_id')->nullable();
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
        Schema::dropIfExists('transaction_sell_lines_clones');
    }
}
