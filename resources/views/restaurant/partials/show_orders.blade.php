<!-- <div class="container">
	<select class="form-control filterData text-center">
	<?php

	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	// $cat = \App\Category::all();
	// echo "<option value='Type' ></option>";
	// foreach ($cat as $ca) {
	// 	echo "<option value='Type" . $ca->id  . "' >" . $ca->name .  "</option>";
	// }
	?>
	</select>
</div> -->
@forelse($orders as $order)
@foreach ($order->sell_lines as $user)
@php
if ($user->parent_sell_line_id > 0 or $user->res_line_order_status == 'cooked') {
continue;
}

if($order->shipping_status == 'ordered')
{
continue;
}
@endphp
<?php
$product = \App\Product::where('id', $user->product_id)->first();
$variation = \App\Variation::where('id', $user->variation_id)->first();
$user_id = Auth::id();
$category_view = \App\category_view::where('user_id', $user_id)->get();
// $category_view = DB::table('category_view')->where('user_id', $user_id)->first();
$category_view_array = [];

foreach ($category_view as $category_view_data) {
	array_push($category_view_array, $category_view_data->category_id);
}
if (!in_array($product->category_id, $category_view_array)) {
	continue;
}
$sell_details = \App\TransactionSellLine::where('id', $user->id)->first();
$sell_adds = \App\TransactionSellLine::where('parent_sell_line_id', $sell_details->id)->get();

try {
	$servie_data_id = \App\Transaction::where('id', $sell_details->transaction_id)->first();
	$service = \App\User::where('id', $servie_data_id->res_waiter_id)->first();
	$name = $service->first_name;
} catch (Exception $e) {
	$name = '';
}
?>
<a href="#" class=" mark_as_cooked_btn" data-href="{{action('Restaurant\KitchenController@markAsCookedOne', [$user->id])}}">
	<div class="col-md-3 col-xs-6 order_div Type{{$product->category_id}}" style="height: 100%;">
		<div class="small-box bg-gray rounded" style="border-radius: 14px!important;">
			<div class="inner">
				<h4 class="text-center">#{{$order->invoice_no}}</h4>
				<table class="table no-margin no-border table-slim text-center" style="color:black!important;text-align:center;">
					<tr>
						<!-- <th>@lang('restaurant.order_status')</th> -->
						<td><span class="label bg-light-blue">@lang('restaurant.order_statuses_new') </span></td>
					</tr>


					<div class="container">
						<tr>
							<!-- <th>@lang('restaurant.placed_at')</th> -->
							<td>{{@format_date($order->created_at)}} {{ @format_time($order->created_at)}}</td>
						</tr>
						<?php
						echo "<tr>";
						// echo "<th>المنتج</th>";
						if ($variation->name != 'DUMMY') {
							echo "<td>" . $product->name .  " " . $variation->name . "<span style='font-weight:bold'>   (" . $sell_details->quantity . ")<span></td>";
						} else {
							echo "<td>" . $product->name . "<span style='font-weight:bold'>   (" . $sell_details->quantity . ")<span></td>";
						}
						echo "</tr>";
						echo "<tr>";
						?>
						<tr>
							<!-- <td>@lang('restaurant.table')</td> -->
							<td><span style="font-weight: bold;">@lang('restaurant.service_staff')</span>({{ $name }} ) <span style="font-weight: bold;">@lang('restaurant.table')</span>({{$order->table_name}})</td>
						</tr>
					</div>
					<?php
					echo "<td> </td>";
					echo "</tr>";
					echo "<tr>";
					// echo "<th style='color:red!important;'>التعليق</th>";
					echo "<td style='color:red!important;'>" . $sell_details->sell_line_note  .  "</td>";
					echo "</tr>";
					// Check adds
					if (count($sell_adds) > 0) {
						foreach ($sell_adds as  $add) {
							$Var = \App\Variation::where('id', $add->variation_id)->first();
							// echo "<th >أضافه</th>";
							echo "<td style='color:orange;font-weight:bold;'>" . $Var->name  .  "</td>";
							echo "</tr>";
						}
					}

					?>
				</table>
			</div>
			@if($orders_for == 'kitchen')
			<!-- <a href="#" class="btn btn-flat small-box-footer bg-yellow mark_as_cooked_btn" data-href="{{action('Restaurant\KitchenController@markAsCooked', [$order->id])}}"><i class="fa fa-check-square-o"></i> @lang('restaurant.mark_as_cooked')</a> -->
			@elseif($orders_for == 'waiter' && $order->res_order_status != 'served')
			<a href="#" class="btn btn-flat small-box-footer bg-yellow mark_as_served_btn" data-href="{{action('Restaurant\OrderController@markAsServed', [$order->id])}}"><i class="fa fa-check-square-o"></i> @lang('restaurant.mark_as_served')</a>
			@else
			<div class="small-box-footer bg-gray">&nbsp;</div>
			@endif
			<a href="#" class="btn btn-flat small-box-footer bg-info btn-modal" data-href="{{ action('SellController@show', [$order->id])}}" data-container=".view_modal">@lang('restaurant.order_details') <i class="fa fa-arrow-circle-right"></i></a>
		</div>
	</div>

	@endforeach

	@if($loop->iteration % 4 == 0)
	<div class="hidden-xs">
		<div class="clearfix"></div>
	</div>
	@endif
	@if($loop->iteration % 2 == 0)
	<div class="visible-xs">
		<div class="clearfix"></div>
	</div>
	@endif
	@empty
	<div class="col-md-12">
		<h4 class="text-center">@lang('restaurant.no_orders_found')</h4>
	</div>
</a>
@endforelse