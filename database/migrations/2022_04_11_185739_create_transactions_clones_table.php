<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsClonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions_clones', function (Blueprint $table) {
            $table->unsignedInteger('id')->default('0')->nullable();
            $table->unsignedInteger('business_id')->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('res_table_id')->nullable()->comment('fields to restaurant module');
            $table->unsignedInteger('res_waiter_id')->nullable()->comment('fields to restaurant module');
            $table->enum('res_order_status', ['received', 'cooked', 'served'])->nullable();
            $table->text('type', 191)->nullable();
            $table->text('sub_type', 20)->nullable();
            $table->text('status', 191)->nullable();
            $table->text('sub_status', 191)->nullable();
            $table->boolean('is_quotation')->nullable()->default(false);
            $table->enum('payment_status', ['paid', 'due', 'partial'])->nullable();
            $table->enum('adjustment_type', ['normal', 'abnormal'])->nullable();
            $table->unsignedInteger('contact_id')->nullable();
            $table->integer('customer_group_id')->nullable()->comment('used to add customer group while selling');
            $table->text('invoice_no', 191)->nullable();
            $table->text('ref_no', 191)->nullable();
            $table->text('source', 191)->nullable();
            $table->text('subscription_no', 191)->nullable();
            $table->text('subscription_repeat_on', 191)->nullable();
            $table->dateTime('transaction_date')->nullable();
            $table->decimal('total_before_tax', 22, 4)->nullable()->default(0)->comment('Total before the purchase/invoice tax, this includeds the indivisual product tax');
            $table->unsignedInteger('tax_id')->nullable();
            $table->decimal('tax_amount', 22, 4)->nullable()->default(0);
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('discount_amount', 22, 4)->nullable()->default(0);
            $table->integer('rp_redeemed')->nullable()->default(0)->comment('rp is the short form of reward points');
            $table->decimal('rp_redeemed_amount', 22, 4)->nullable()->default(0)->comment('rp is the short form of reward points');
            $table->text('shipping_details', 191)->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('shipping_status', 191)->nullable();
            $table->text('delivered_to', 191)->nullable();
            $table->decimal('shipping_charges', 22, 4)->nullable()->default(0);
            $table->text('shipping_custom_field_1', 191)->nullable();
            $table->text('shipping_custom_field_2', 191)->nullable();
            $table->text('shipping_custom_field_3', 191)->nullable();
            $table->text('shipping_custom_field_4', 191)->nullable();
            $table->text('shipping_custom_field_5', 191)->nullable();
            $table->text('additional_notes')->nullable();
            $table->text('staff_note')->nullable();
            $table->boolean('is_export')->nullable()->default(false);
            $table->longText('export_custom_fields_info')->nullable();
            $table->decimal('round_off_amount', 22, 4)->nullable()->default(0)->comment('Difference of rounded total and actual total');
            $table->text('additional_expense_key_1', 191)->nullable();
            $table->decimal('additional_expense_value_1', 22, 4)->nullable()->default(0);
            $table->text('additional_expense_key_2', 191)->nullable();
            $table->decimal('additional_expense_value_2', 22, 4)->nullable()->default(0);
            $table->text('additional_expense_key_3', 191)->nullable();
            $table->decimal('additional_expense_value_3', 22, 4)->nullable()->default(0);
            $table->text('additional_expense_key_4', 191)->nullable();
            $table->decimal('additional_expense_value_4', 22, 4)->nullable()->default(0);
            $table->decimal('final_total', 22, 4)->nullable()->default(0);
            $table->unsignedInteger('expense_category_id')->nullable();
            $table->unsignedInteger('expense_for')->nullable();
            $table->integer('commission_agent')->nullable();
            $table->text('document', 191)->nullable();
            $table->boolean('is_direct_sale')->nullable()->default(false);
            $table->boolean('is_suspend')->nullable()->default(false);
            $table->decimal('exchange_rate', 20, 3)->default(1);
            $table->decimal('total_amount_recovered', 22, 4)->nullable()->comment('Used for stock adjustment.');
            $table->integer('transfer_parent_id')->nullable();
            $table->integer('return_parent_id')->nullable();
            $table->integer('opening_stock_product_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->text('prefer_payment_method', 191)->nullable();
            $table->integer('prefer_payment_account')->nullable();
            $table->text('sales_order_ids')->nullable();
            $table->text('purchase_order_ids')->nullable();
            $table->text('custom_field_1', 191)->nullable();
            $table->text('custom_field_2', 191)->nullable();
            $table->text('custom_field_3', 191)->nullable();
            $table->text('custom_field_4', 191)->nullable();
            $table->integer('import_batch')->nullable();
            $table->dateTime('import_time')->nullable();
            $table->integer('types_of_service_id')->nullable();
            $table->decimal('packing_charge', 22, 4)->nullable();
            $table->enum('packing_charge_type', ['fixed', 'percent'])->nullable();
            $table->text('service_custom_field_1')->nullable();
            $table->text('service_custom_field_2')->nullable();
            $table->text('service_custom_field_3')->nullable();
            $table->text('service_custom_field_4')->nullable();
            $table->text('service_custom_field_5')->nullable();
            $table->text('service_custom_field_6')->nullable();
            $table->boolean('is_created_from_api')->default(false);
            $table->decimal('essentials_duration');
            $table->text('essentials_duration_unit', 20)->nullable();
            $table->decimal('essentials_amount_per_unit_duration', 22, 4)->nullable()->default(0);
            $table->text('essentials_allowances')->nullable();
            $table->text('essentials_deductions')->nullable();
            $table->integer('rp_earned')->default(0)->comment('rp is the short form of reward points');
            $table->text('order_addresses')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->double('recur_interval', 22, 4)->nullable();
            $table->enum('recur_interval_type', ['days', 'months', 'years'])->nullable();
            $table->integer('recur_repetitions')->nullable();
            $table->dateTime('recur_stopped_on')->nullable();
            $table->integer('recur_parent_id')->nullable();
            $table->text('invoice_token', 191)->nullable();
            $table->integer('pay_term_number')->nullable();
            $table->enum('pay_term_type', ['days', 'months'])->nullable();
            $table->integer('selling_price_group_id')->nullable();
            $table->timestamps();

            // $table->unsignedInteger('id')->default('0');
            // $table->unsignedInteger('business_id');
            // $table->unsignedInteger('location_id')->nullable();
            // $table->unsignedInteger('res_table_id')->nullable()->comment('fields to restaurant module');
            // $table->unsignedInteger('res_waiter_id')->nullable()->comment('fields to restaurant module');
            // $table->enum('res_order_status', ['received', 'cooked', 'served'])->nullable();
            // $table->string('type', 191)->nullable();
            // $table->string('sub_type', 20)->nullable();
            // $table->string('status', 191);
            // $table->string('sub_status', 191)->nullable();
            // $table->boolean('is_quotation')->default(false);
            // $table->enum('payment_status', ['paid', 'due', 'partial'])->nullable();
            // $table->enum('adjustment_type', ['normal', 'abnormal'])->nullable();
            // $table->unsignedInteger('contact_id')->nullable();
            // $table->integer('customer_group_id')->nullable()->comment('used to add customer group while selling');
            // $table->string('invoice_no', 191)->nullable();
            // $table->string('ref_no', 191)->nullable();
            // $table->string('source', 191)->nullable();
            // $table->string('subscription_no', 191)->nullable();
            // $table->string('subscription_repeat_on', 191)->nullable();
            // $table->dateTime('transaction_date');
            // $table->decimal('total_before_tax', 22, 4)->default(0)->comment('Total before the purchase/invoice tax, this includeds the indivisual product tax');
            // $table->unsignedInteger('tax_id')->nullable();
            // $table->decimal('tax_amount', 22, 4)->default(0);
            // $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            // $table->decimal('discount_amount', 22, 4)->nullable()->default(0);
            // $table->integer('rp_redeemed')->default(0)->comment('rp is the short form of reward points');
            // $table->decimal('rp_redeemed_amount', 22, 4)->default(0)->comment('rp is the short form of reward points');
            // $table->string('shipping_details', 191)->nullable();
            // $table->text('shipping_address')->nullable();
            // $table->string('shipping_status', 191)->nullable();
            // $table->string('delivered_to', 191)->nullable();
            // $table->decimal('shipping_charges', 22, 4)->default(0);
            // $table->string('shipping_custom_field_1', 191)->nullable();
            // $table->string('shipping_custom_field_2', 191)->nullable();
            // $table->string('shipping_custom_field_3', 191)->nullable();
            // $table->string('shipping_custom_field_4', 191)->nullable();
            // $table->string('shipping_custom_field_5', 191)->nullable();
            // $table->text('additional_notes')->nullable();
            // $table->text('staff_note')->nullable();
            // $table->boolean('is_export')->default(false);
            // $table->longText('export_custom_fields_info')->nullable();
            // $table->decimal('round_off_amount', 22, 4)->default(0)->comment('Difference of rounded total and actual total');
            // $table->string('additional_expense_key_1', 191)->nullable();
            // $table->decimal('additional_expense_value_1', 22, 4)->default(0);
            // $table->string('additional_expense_key_2', 191)->nullable();
            // $table->decimal('additional_expense_value_2', 22, 4)->default(0);
            // $table->string('additional_expense_key_3', 191)->nullable();
            // $table->decimal('additional_expense_value_3', 22, 4)->default(0);
            // $table->string('additional_expense_key_4', 191)->nullable();
            // $table->decimal('additional_expense_value_4', 22, 4)->default(0);
            // $table->decimal('final_total', 22, 4)->default(0);
            // $table->unsignedInteger('expense_category_id')->nullable();
            // $table->unsignedInteger('expense_for')->nullable();
            // $table->integer('commission_agent')->nullable();
            // $table->string('document', 191)->nullable();
            // $table->boolean('is_direct_sale')->default(false);
            // $table->boolean('is_suspend')->default(false);
            // $table->decimal('exchange_rate', 20, 3)->default(1);
            // $table->decimal('total_amount_recovered', 22, 4)->nullable()->comment('Used for stock adjustment.');
            // $table->integer('transfer_parent_id')->nullable();
            // $table->integer('return_parent_id')->nullable();
            // $table->integer('opening_stock_product_id')->nullable();
            // $table->unsignedInteger('created_by');
            // $table->string('prefer_payment_method', 191)->nullable();
            // $table->integer('prefer_payment_account')->nullable();
            // $table->text('sales_order_ids')->nullable();
            // $table->text('purchase_order_ids')->nullable();
            // $table->string('custom_field_1', 191)->nullable();
            // $table->string('custom_field_2', 191)->nullable();
            // $table->string('custom_field_3', 191)->nullable();
            // $table->string('custom_field_4', 191)->nullable();
            // $table->integer('import_batch')->nullable();
            // $table->dateTime('import_time')->nullable();
            // $table->integer('types_of_service_id')->nullable();
            // $table->decimal('packing_charge', 22, 4)->nullable();
            // $table->enum('packing_charge_type', ['fixed', 'percent'])->nullable();
            // $table->text('service_custom_field_1')->nullable();
            // $table->text('service_custom_field_2')->nullable();
            // $table->text('service_custom_field_3')->nullable();
            // $table->text('service_custom_field_4')->nullable();
            // $table->text('service_custom_field_5')->nullable();
            // $table->text('service_custom_field_6')->nullable();
            // $table->boolean('is_created_from_api')->default(false);
            // $table->decimal('essentials_duration');
            // $table->string('essentials_duration_unit', 20)->nullable();
            // $table->decimal('essentials_amount_per_unit_duration', 22, 4)->default(0);
            // $table->text('essentials_allowances')->nullable();
            // $table->text('essentials_deductions')->nullable();
            // $table->integer('rp_earned')->default(0)->comment('rp is the short form of reward points');
            // $table->text('order_addresses')->nullable();
            // $table->boolean('is_recurring')->default(false);
            // $table->double('recur_interval', 22, 4)->nullable();
            // $table->enum('recur_interval_type', ['days', 'months', 'years'])->nullable();
            // $table->integer('recur_repetitions')->nullable();
            // $table->dateTime('recur_stopped_on')->nullable();
            // $table->integer('recur_parent_id')->nullable();
            // $table->string('invoice_token', 191)->nullable();
            // $table->integer('pay_term_number')->nullable();
            // $table->enum('pay_term_type', ['days', 'months'])->nullable();
            // $table->integer('selling_price_group_id')->nullable();
            // $table->timestamps();

        


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions_clones');
    }
}
