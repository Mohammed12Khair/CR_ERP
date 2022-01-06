@if(isset($read_only))
<div class="payment_details_div" data-type="cheque_accept">
	@if(!empty($accounts))
	<div class="col-md-6">
		<div class="form-group">
			{!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
			<!-- Cash -->
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::select("account_id", $accounts_cheques, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;' , 'required']); !!}

			</div>
		</div>
	</div>
	@endif
</div>
@else
<div class="payment_details_div @if( $payment_line->method !== 'cheque_accept' ) {{ 'hide' }} @endif" data-type="cheque_accept">
	@if(!empty($accounts))
	<div class="col-md-6">
		<div class="form-group">
			{!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
			<!-- Cash -->
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::select("account_id", $accounts_cheques, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;' , 'required']); !!}

			</div>
		</div>
	</div>
	@endif
</div>
<div class="payment_details_div @if( $payment_line->method !== 'cash' ) {{ 'hide' }} @endif" data-type="cash">
	@if(!empty($accounts))
	<div class="col-md-6">
		<div class="form-group">
			{!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
			<!-- Cash -->
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::select("account_id", $accounts, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;' , 'required']); !!}
			</div>
		</div>
	</div>
	@endif


</div>
<div class="payment_details_div @if( $payment_line->method !== 'card' ) {{ 'hide' }} @endif" data-type="card">
	@if(!empty($accounts))
	<div class="col-md-6">
		<div class="form-group">
			{!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
			<!-- Cash -->
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::select("account_id", $accounts, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;' , 'required']); !!}
			</div>
		</div>
	</div>
	@endif




	<div class="col-md-4">
		<div class="form-group">
			{!! Form::label("card_number", __('lang_v1.card_no')) !!}
			{!! Form::text("card_number", $payment_line->card_number, ['class' => 'form-control', 'placeholder' => __('lang_v1.card_no')]); !!}
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{!! Form::label("card_holder_name", __('lang_v1.card_holder_name')) !!}
			{!! Form::text("card_holder_name", $payment_line->card_holder_name, ['class' => 'form-control', 'placeholder' => __('lang_v1.card_holder_name')]); !!}
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{!! Form::label("card_transaction_number",__('lang_v1.card_transaction_no')) !!}
			{!! Form::text("card_transaction_number", $payment_line->card_transaction_number, ['class' => 'form-control', 'placeholder' => __('lang_v1.card_transaction_no')]); !!}
		</div>
	</div>
	<div class="clearfix"></div>
	<div class="col-md-3">
		<div class="form-group">
			{!! Form::label("card_type", __('lang_v1.card_type')) !!}
			{!! Form::select("card_type", ['credit' => 'Credit Card', 'debit' => 'Debit Card', 'visa' => 'Visa', 'master' => 'MasterCard'], $payment_line->card_type,['class' => 'form-control select2']); !!}
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			{!! Form::label("card_month", __('lang_v1.month')) !!}
			{!! Form::text("card_month", $payment_line->card_month, ['class' => 'form-control',
			'placeholder' => __('lang_v1.month') ]); !!}
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			{!! Form::label("card_year", __('lang_v1.year')) !!}
			{!! Form::text("card_year", $payment_line->card_year, ['class' => 'form-control', 'placeholder' => __('lang_v1.year') ]); !!}
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			{!! Form::label("card_security",__('lang_v1.security_code')) !!}
			{!! Form::text("card_security", $payment_line->card_security, ['class' => 'form-control', 'placeholder' => __('lang_v1.security_code')]); !!}
		</div>
	</div>
	<div class="clearfix"></div>
</div>
<div class="payment_details_div @if( $payment_line->method !== 'cheque' ) {{ 'hide' }} @endif" data-type="cheque">
	@if(!empty($accounts))
	<div class="col-md-6">
		<div class="form-group">
			{!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
			<!-- Cash -->
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::select("account_id", $accounts_cheques, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;' , 'required']); !!}

			</div>
		</div>
	</div>
	@endif


	<!-- edit Data -->
	<div class="col-md-12">
		<div class="form-group">
			{!! Form::label("cheque_date",__('lang_v1.cheque_date')) !!}
			{!! Form::date("cheque_date", $payment_line->cheque_date, ['class' => 'form-control', 'placeholder' => __('lang_v1.cheque_date'),'required']); !!}
		</div>
	</div>
	<div class="col-md-12">
		<div class="form-group">
			{!! Form::label("cheque_number",__('lang_v1.cheque_no')) !!}
			{!! Form::text("cheque_number", $payment_line->cheque_number, ['class' => 'form-control', 'placeholder' => __('lang_v1.cheque_no'),'required']); !!}
		</div>
	</div>
</div>


<div class="payment_details_div @if( $payment_line->method !== 'bank_transfer' ) {{ 'hide' }} @endif" data-type="bank_transfer">
	@if(!empty($accounts))
	<div class="col-md-6">
		<div class="form-group">
			{!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
			<!-- Cash -->
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::select("account_id", $accounts, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;' , 'required']); !!}
			</div>
		</div>
	</div>
	@endif



	<div class="col-md-12">
		<div class="form-group">
			{!! Form::label("bank_account_number",__('lang_v1.bank_account_number')) !!}
			{!! Form::text( "bank_account_number", $payment_line->bank_account_number, ['class' => 'form-control', 'placeholder' => __('lang_v1.bank_account_number')]); !!}
		</div>
	</div>
</div>
<div class="payment_details_div @if( $payment_line->method !== 'custom_pay_1' ) {{ 'hide' }} @endif" data-type="custom_pay_1">
	@if(!empty($accounts))
	<div class="col-md-6">
		<div class="form-group">
			{!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
			<!-- Cash -->
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::select("account_id", $accounts, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;' , 'required']); !!}
			</div>
		</div>
	</div>
	@endif



	<div class="col-md-12">
		<div class="form-group">
			{!! Form::label("transaction_no_1", __('lang_v1.transaction_no')) !!}
			{!! Form::text("transaction_no_1", $payment_line->transaction_no, ['class' => 'form-control', 'placeholder' => __('lang_v1.transaction_no')]); !!}
		</div>
	</div>
</div>
<div class="payment_details_div @if( $payment_line->method !== 'custom_pay_2' ) {{ 'hide' }} @endif" data-type="custom_pay_2">
	@if(!empty($accounts))
	<div class="col-md-6">
		<div class="form-group">
			{!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
			<!-- Cash -->
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::select("account_id", $accounts, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;' , 'required']); !!}
			</div>
		</div>
	</div>
	@endif



	<div class="col-md-12">
		<div class="form-group">
			{!! Form::label("transaction_no_2", __('lang_v1.transaction_no')) !!}
			{!! Form::text("transaction_no_2", $payment_line->transaction_no, ['class' => 'form-control', 'placeholder' => __('lang_v1.transaction_no')]); !!}
		</div>
	</div>
</div>
<div class="payment_details_div @if( $payment_line->method !== 'custom_pay_3' ) {{ 'hide' }} @endif" data-type="custom_pay_3">
	@if(!empty($accounts))
	<div class="col-md-6">
		<div class="form-group">
			{!! Form::label("account_id" , __('lang_v1.payment_account') . ':') !!}
			<!-- Cash -->
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fas fa-money-bill-alt"></i>
				</span>
				{!! Form::select("account_id", $accounts, !empty($payment_line->account_id) ? $payment_line->account_id : '' , ['class' => 'form-control select2', 'id' => "account_id", 'style' => 'width:100%;' , 'required']); !!}
			</div>
		</div>
	</div>
	@endif



	<div class="col-md-12">
		<div class="form-group">
			{!! Form::label("transaction_no_3", __('lang_v1.transaction_no')) !!}
			{!! Form::text("transaction_no_3", $payment_line->transaction_no, ['class' => 'form-control', 'placeholder' => __('lang_v1.transaction_no')]); !!}
		</div>
	</div>
</div>

@endif