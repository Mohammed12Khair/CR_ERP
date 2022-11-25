<div>
	<button type="button" class="btn btn-primary no-print" aria-label="Print" onclick="$(this).closest('div').printThis();">
		<i class="fa fa-print"></i> @lang( 'messages.print' )
	</button>

	<div class="row">

		<div class="col-md-12">
			<h4>{{$stock_details['variation']}}</h4>
		</div>
		<div class="col-md-4 col-xs-4">
			<strong>@lang('lang_v1.quantities_in')</strong>
			<table class="table table-condensed">
				<tr>
					<th>@lang('report.total_purchase')</th>
					<td>
						<span class="display_currency" data-is_quantity="true">{{$stock_details['total_purchase']}}</span> {{$stock_details['unit']}}
					</td>
				</tr>
				<tr>
					<th>@lang('lang_v1.opening_stock')</th>
					<td>
						<span class="display_currency" data-is_quantity="true">{{$stock_details['total_opening_stock']}}</span> {{$stock_details['unit']}}
					</td>
				</tr>
				<tr>
					<th>@lang('lang_v1.total_sell_return')</th>
					<td>
						<span class="display_currency" data-is_quantity="true">{{$stock_details['total_sell_return']}}</span> {{$stock_details['unit']}}
					</td>
				</tr>
				<tr>
					<th>@lang('lang_v1.stock_transfers') (@lang('lang_v1.in'))</th>
					<td>
						<span class="display_currency" data-is_quantity="true">{{$stock_details['total_purchase_transfer']}}</span> {{$stock_details['unit']}}
					</td>
				</tr>
			</table>
		</div>
		<div class="col-md-4 col-xs-4">
			<strong>@lang('lang_v1.quantities_out')</strong>
			<table class="table table-condensed">
				<tr>
					<th>@lang('lang_v1.total_sold')</th>
					<td>
						<span class="display_currency" data-is_quantity="true">{{$stock_details['total_sold']}}</span> {{$stock_details['unit']}}
					</td>
				</tr>
				<tr>
					<th>@lang('report.total_stock_adjustment')</th>
					<td>
						<span class="display_currency" data-is_quantity="true">{{$stock_details['total_adjusted']}}</span> {{$stock_details['unit']}}
					</td>
				</tr>
				<tr>
					<th>@lang('lang_v1.total_purchase_return')</th>
					<td>
						<span class="display_currency" data-is_quantity="true">{{$stock_details['total_purchase_return']}}</span> {{$stock_details['unit']}}
					</td>
				</tr>

				<tr>
					<th>@lang('lang_v1.stock_transfers') (@lang('lang_v1.out'))</th>
					<td>
						<span class="display_currency" data-is_quantity="true">{{$stock_details['total_sell_transfer']}}</span> {{$stock_details['unit']}}
					</td>
				</tr>
			</table>
		</div>

		<div class="col-md-4 col-xs-4">
			<strong>@lang('lang_v1.totals')</strong>
			<table class="table table-condensed">
				<tr>
					<th>@lang('report.current_stock')</th>
					<td>
						<span class="display_currency" data-is_quantity="true">{{$stock_details['current_stock']}}</span> {{$stock_details['unit']}}
					</td>
				</tr>
			</table>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<hr>
			<table class="table table-slim" id="stock_history_table">
				<thead>
					<tr>
						<th>@lang('lang_v1.date')</th>
						<th>@lang('lang_v1.type')</th>
						<th>@lang('lang_v1.quantity_change')</th>
						<th>@lang('lang_v1.new_quantity')</th>
						<!-- <th>payment status</th> -->
						<th>العميل/المورد</th>
						<th> المندوب</th>
						<th>@lang('purchase.payment_status')</th>
						<th>@lang('purchase.ref_no')</th>
						<th>@lang('account.view_details')</th>
					</tr>
				</thead>
				<tbody>
					@forelse($stock_history as $history)
					<?php
					$selectref_no = App\Transaction::where('ref_no', $history['ref_no'])->count();
					$selectInvoice = App\Transaction::where('invoice_no', $history['ref_no'])->count();
					if ($selectref_no > 0) {
						$Type = App\Transaction::where('ref_no', $history['ref_no'])->first()->type;
						$ID = App\Transaction::where('ref_no', $history['ref_no'])->first()->id;
						$return_parent_id = App\Transaction::where('ref_no', $history['ref_no'])->first()->return_parent_id;
						$link = "";
						if ($Type == "purchase") {
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/purchases/' . $ID . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
						if ($Type == "sell") {
							// <a href="#" data-href="http://kaizengroups.net/test/dev/public/sells/2" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> عرض</a>
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/sells/' . $ID . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
						if ($Type == "stock_adjustment") {
							// <button type="button" data-href="http://kaizengroups.net/test/dev/public/stock-adjustments/8" class="btn btn-primary btn-xs btn-modal" data-container=".view_modal"><i class="fa fa-eye" aria-hidden="true"></i> عرض</button>
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/stock-adjustments/' . $ID . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
						if ($Type == "sell_transfer") {
							// <button type="button" data-href="http://kaizengroups.net/test/dev/public/stock-adjustments/8" class="btn btn-primary btn-xs btn-modal" data-container=".view_modal"><i class="fa fa-eye" aria-hidden="true"></i> عرض</button>
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/stock-transfers/' . $ID . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
						if ($Type == "purchase_return") {
							// <button type="button" data-href="http://kaizengroups.net/test/dev/public/stock-adjustments/8" class="btn btn-primary btn-xs btn-modal" data-container=".view_modal"><i class="fa fa-eye" aria-hidden="true"></i> عرض</button>
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/purchase-return/' . $ID . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
						if ($Type == "sell_return") {
							// <button type="button" data-href="http://kaizengroups.net/test/dev/public/stock-adjustments/8" class="btn btn-primary btn-xs btn-modal" data-container=".view_modal"><i class="fa fa-eye" aria-hidden="true"></i> عرض</button>
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/sell-return/' . $return_parent_id . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
					} else {
						$Type = App\Transaction::where('invoice_no', $history['ref_no'])->first()->type;
						$ID = App\Transaction::where('invoice_no', $history['ref_no'])->first()->id;
						$return_parent_id = App\Transaction::where('invoice_no', $history['ref_no'])->first()->return_parent_id;
						$link = "";
						if ($Type == "purchase") {
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/purchases/' . $ID . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
						if ($Type == "sell") {
							// <a href="#" data-href="http://kaizengroups.net/test/dev/public/sells/2" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> عرض</a>
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/sells/' . $ID . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
						if ($Type == "stock_adjustment") {
							// <button type="button" data-href="http://kaizengroups.net/test/dev/public/stock-adjustments/8" class="btn btn-primary btn-xs btn-modal" data-container=".view_modal"><i class="fa fa-eye" aria-hidden="true"></i> عرض</button>
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/stock-adjustments/' . $ID . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
						if ($Type == "sell_transfer") {
							// <button type="button" data-href="http://kaizengroups.net/test/dev/public/stock-adjustments/8" class="btn btn-primary btn-xs btn-modal" data-container=".view_modal"><i class="fa fa-eye" aria-hidden="true"></i> عرض</button>
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/stock-transfers/' . $ID . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
						if ($Type == "purchase_return") {
							// <button type="button" data-href="http://kaizengroups.net/test/dev/public/stock-adjustments/8" class="btn btn-primary btn-xs btn-modal" data-container=".view_modal"><i class="fa fa-eye" aria-hidden="true"></i> عرض</button>
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/purchase-return/' . $ID . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
						if ($Type == "sell_return") {
							// <button type="button" data-href="http://kaizengroups.net/test/dev/public/stock-adjustments/8" class="btn btn-primary btn-xs btn-modal" data-container=".view_modal"><i class="fa fa-eye" aria-hidden="true"></i> عرض</button>
							$link = '<a href="#" data-href="http://kaizengroups.net/test/dev/public/sell-return/' . $return_parent_id . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i>عرض</a>';
						}
					}

					?>
					<tr>
						<td>{{@format_datetime($history['date'])}}</td>

						<td>{{$history['type_label']}}</td>
						<td>@if($history['quantity_change'] > 0 ) +<span class="display_currency" data-is_quantity="true">{{$history['quantity_change']}}</span> @else <span class="display_currency" data-is_quantity="true">{{$history['quantity_change']}}</span> @endif</td>
						<td><span class="display_currency" data-is_quantity="true">{{$history['stock']}}</span></td>
						<td><?php
							if ($selectref_no > 0) {
								$Details = App\Transaction::where('ref_no', $history['ref_no'])->first();
								// echo $Details->contact_id;
								echo App\Contact::where('id', $Details->contact_id)->first()->name;
							} else {
								$Details = App\Transaction::where('invoice_no', $history['ref_no'])->first();
								// echo $Details->contact_id;
								echo App\Contact::where('id', $Details->contact_id)->first()->name;
							}
							?></td>
						<td><?php
							$AgendID = App\Transaction::where('id', $ID)->first()->commission_agent;
							// echo $AgendID;
							echo App\User::where('id', $AgendID)->first()->first_name;
							?></td>
						<td><?php
							if ($Type == "purchase" || $Type == "sell") {
								if ($selectref_no > 0) {
									echo __('lang_v1.' . App\Transaction::where('ref_no', $history['ref_no'])->first()->payment_status );
								} else {
									echo  __('lang_v1.' . App\Transaction::where('invoice_no', $history['ref_no'])->first()->payment_status);
								}
							}
							?></td>
						<td>{{$history['ref_no']}}</td>
						<td><?php echo $link; ?></td>
					</tr>
					@empty
					<tr>
						<td colspan="5" class="text-center">
							@lang('lang_v1.no_stock_history_found')
						</td>
					</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>