<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaselinesClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchaselines_clones', function (Blueprint $table) {
            $table->unsignedInteger('id')->default('0');
            $table->unsignedInteger('transaction_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variation_id');
            $table->decimal('quantity', 22, 4)->default(0);
            $table->decimal('pp_without_discount', 22, 4)->default(0)->comment('Purchase price before inline discounts');
            $table->decimal('discount_percent', 5)->default(0)->comment('Inline discount percentage');
            $table->decimal('purchase_price', 22, 4);
            $table->decimal('purchase_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->comment('Tax for one quantity');
            $table->unsignedInteger('tax_id')->nullable();
            $table->integer('purchase_order_line_id')->nullable();
            $table->decimal('quantity_sold', 22, 4)->default(0)->comment('Quanity sold from this purchase line');
            $table->decimal('quantity_adjusted', 22, 4)->default(0)->comment('Quanity adjusted in stock adjustment from this purchase line');
            $table->decimal('quantity_returned', 22, 4)->default(0);
            $table->decimal('po_quantity_purchased', 22, 4)->default(0);
            $table->decimal('mfg_quantity_used', 22, 4)->default(0);
            $table->date('mfg_date')->nullable();
            $table->date('exp_date')->nullable();
            $table->string('lot_number', 191)->nullable();
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
        Schema::dropIfExists('purchaselines_clones');
    }
}
