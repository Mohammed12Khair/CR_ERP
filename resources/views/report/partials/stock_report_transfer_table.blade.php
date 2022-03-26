<?php

use App\Mail\ExceptionOccured;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Sum;


function get_location_name($location_id)
{
    $locatoin = App\BusinessLocation::where('id', $location_id)->first();
    return  $locatoin->name;
}


function get_product_name($product_id)
{
    $product = App\Product::where('id', $product_id)->first();
    return $product->name;
}


function get_product_price($product_id)
{
    try {
        $price = App\Variation::where('product_id', $product_id)->first();
        return $price->default_purchase_price;
    } catch (Exception $e) {
        return '';
    }
}


function get_details($product_id, $location_id)
{
    try {
        $transaction_id_ = 0;
        $Transfer_action_ = DB::select(DB::raw("SELECT product_id product_id,max(transaction_id) transaction_id FROM purchase_lines
where product_id in (:products) and transaction_id in (select id from transactions where 
type='purchase_transfer' and location_id in (:location_id)) group by product_id"), ["products" => $product_id, "location_id" => $location_id]);
        foreach ($Transfer_action_ as $Transfer_action) {
            // echo $Transfer_action->product_id . '-' . $Transfer_action->transaction_id . ' <br>';
            $transaction_id_ = $Transfer_action->transaction_id;
        }
        // Get Empensis
        // Get additional 
        $info_ = App\Transaction::where('id', $transaction_id_)->select('additional_notes', 'transfer_parent_id', 'location_id')->first();
        $expense_ = App\Transaction::where('additional_notes', $info_->additional_notes)->where('type', 'expense')->get();

        $expense_value = 0;
        foreach ($expense_ as $expense) {
            $expense_value += $expense->final_total;
        }

        // echo 'Expense=' . $expense_value . "<br>";

        // Get all transaction Details
        $Transfer_transaction_ = App\PurchaseLine::where('transaction_id', $transaction_id_)->get();
        $total_quantati = 0;
        foreach ($Transfer_transaction_ as $Transfer_transaction) {
            $total_quantati += $Transfer_transaction->quantity;
        }
        // echo 'Total Quantity=' . $total_quantati . '<br>';

        $location_2 = App\Transaction::where('id', $info_->transfer_parent_id)->first();
        // echo $info_->transfer_parent_id;
        foreach ($Transfer_transaction_ as  $Transfer_transaction) {
            if ($Transfer_transaction->product_id  == $product_id) {
                $a=round((($Transfer_transaction->quantity / $total_quantati) * $expense_value) / $Transfer_transaction->quantity, 2);
                $b=round(get_product_price($Transfer_transaction->product_id), 2);
                $c=$a+$b;
                echo "<tr>";
                echo "<td>" . get_location_name($location_2->location_id) . "</td>";
                echo "<td>" . get_location_name($info_->location_id) . "</td>";
                echo "<td>" .  get_product_name($Transfer_transaction->product_id) . "</td>";
                echo "<td>" .  $Transfer_transaction->created_at . "</td>";
                echo "<td>" . round(get_product_price($Transfer_transaction->product_id), 2) . "</td>";
                echo "<td>" . round((($Transfer_transaction->quantity / $total_quantati) * $expense_value) / $Transfer_transaction->quantity, 2) . "</td>";
                echo "<td>" .  $c  . "</td>";
                // echo $location_id . ' ' .  " Product_id=" .  $Transfer_transaction->product_id  . ' Transfer_Quantati=' . $Transfer_transaction->quantity . ' %=' . (($Transfer_transaction->quantity / $total_quantati) * $expense_value) / $Transfer_transaction->quantity . '<br>';
                echo "</tr>";
            }
            // echo $Transfer_transaction->product_id . ' %=' . ($Transfer_transaction->quantity / $total_quantati) * $expense_value . '<br>';
        }
    } catch (Exception $e) {
        return '';
    }
}
?>
<!-- 
<table class="table table-bordered table-striped" id="stock_report_tablde" style="width: 100%;">
    <thead>
        <tr>
            <th>SKU</th>
            <th>@lang('business.product')</th>
            <th>@lang('sale.location')</th>
            <th>@lang('sale.unit_price')</th>
            <th>@lang('report.current_stock')</th>
            @can('view_product_stock_value')
            <th class="stock_price">@lang('lang_v1.total_stock_price') <br><small>(@lang('lang_v1.by_purchase_price'))</small></th>
            <th>@lang('lang_v1.total_stock_price') <br><small>(@lang('lang_v1.by_sale_price'))</small></th>
            <th>@lang('lang_v1.potential_profit')</th>
            @endcan
            <th>@lang('report.total_unit_sold')</th>
            <th>@lang('lang_v1.total_unit_transfered')</th>
            <th>@lang('lang_v1.total_unit_adjusted')</th>
            @if($show_manufacturing_data)
                <th class="current_stock_mfg">@lang('manufacturing::lang.current_stock_mfg') @show_tooltip(__('manufacturing::lang.mfg_stock_tooltip'))</th>
            @endif
        </tr>
    </thead>
    <tfoot>
        <tr class="bg-gray font-17 text-center footer-total">
            <td colspan="4"><strong>@lang('sale.total'):</strong></td>
            <td class="footer_total_stock"></td>
            @can('view_product_stock_value')
            <td class="footer_total_stock_price"></td>
            <td class="footer_stock_value_by_sale_price"></td>
            <td class="footer_potential_profit"></td>
            @endcan
            <td class="footer_total_sold"></td>
            <td class="footer_total_transfered"></td>
            <td class="footer_total_adjusted"></td>
            @if($show_manufacturing_data)
                <td class="footer_total_mfg_stock"></td>
            @endif
        </tr>
    </tfoot>
</table> -->
<!-- $('#stock_report_table_transfer').DataTable -->
<table class="table table-bordered table-striped" id="stock_report_table_transfer" style="width: 100%;text-align:center;">
    <tr>
        <th>@lang('product.from')</th>
        <th>@lang('product.to')</th>
        <th>@lang('product.name')</th>
        <th>@lang('product.t_date')</th>
        <th>@lang('product.priceCostBuy')</th>
        <th>@lang('product.priceCost')</th>
        <th>@lang('product.priceCost') + @lang('product.priceCostBuy')</th>
    </tr>
    <?php
    // GEt locations
    $locations_ = App\BusinessLocation::where('business_id', session()->get('user.business_id'))->get();
    // Get products
    $products_ = App\Product::where('business_id', session()->get('user.business_id'))->get();
    foreach ($products_  as $products) {
        foreach ($locations_  as $locations) {
            get_details($products->id, $locations->id);
        }
    }
    ?>
</table>