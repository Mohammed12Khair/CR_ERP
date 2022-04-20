<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action('TransactionPaymentController@postPayContactDue_partner'), 'method' => 'post', 'id' => 'pay_contact_due_form', 'files' => true]) !!}

        
        {!! Form::hidden('account_transaction_id', $account_transaction_id) !!}
        {!! Form::hidden('due_payment_type', $due_payment_type) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'purchase.add_payment' )</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                @if ($due_payment_type == 'purchase')
                    <div class="col-md-6">
                        <div class="well">
                         </div>
                    </div>
                    <div class="col-md-6">
                        <div class="well">
                         
                        </div>
                    </div>
                @elseif($due_payment_type == 'purchase_return')
                    <div class="col-md-6">
                        <div class="well">
                          </div>
                    </div>
                    <div class="col-md-6">
                        <div class="well">
                        </div>
                    </div>
                @elseif(in_array($due_payment_type, ['sell']))
                    <div class="col-md-6">
                        <div class="well">
                             <br><br>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="well">
                         
                        </div>
                    </div>
                @elseif(in_array($due_payment_type, ['sell_return']))
                    <div class="col-md-6">
                        <div class="well">
                            <br><br>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="well">
                       </div>
                    </div>
                @endif
            </div>
            <div class="row payment_row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('amount', __('sale.amount') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            @if (in_array($due_payment_type, ['sell_return', 'purchase_return']))
                                {!! Form::text('amount', @num_format($payment_line->amount), ['max_value' => $max,'data-rule-max-value' => @num_format($max),'data-msg-max-value' => __('lang_v1.max_amount_to_be_paid_is', ['max' => $max]),'class' => 'form-control input_number', 'required', 'placeholder' => __('sale.amount'), 'data-rule-max-value' => $payment_line->amount, 'data-msg-max-value' => __('lang_v1.max_amount_to_be_paid_is', ['amount' => $amount_formated])]) !!}
                            @else
                                {!! Form::text('amount', @num_format($payment_line->amount), ['max_value' => $max, 'data-rule-max-value' => @num_format($max),'data-msg-max-value' => __('lang_v1.max_amount_to_be_paid_is', ['max' => $max]),'class' => 'form-control input_number', 'required', 'placeholder' => __('sale.amount')]) !!}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('paid_on', __('lang_v1.paid_on') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('paid_on', @format_datetime($payment_line->paid_on), ['class' => 'form-control', 'readonly', 'required']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('method', __('purchase.payment_method') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-money-bill-alt"></i>
                            </span>
                            {!! Form::select('method', $payment_types, $payment_line->method, ['class' => 'form-control select2 payment_types_dropdown', 'required', 'style' => 'width:100%;']) !!}
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                        {!! Form::file('document', ['accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]) !!}
                        <p class="help-block">
                            @includeIf('components.document_help_text')</p>
                    </div>
                </div>
                @if (!empty($accounts))
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('account_id', __('lang_v1.payment_account') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fas fa-money-bill-alt"></i>
                                </span>
                                {!! Form::select('account_id', $accounts, !empty($payment_line->account_id) ? $payment_line->account_id : '', ['class' => 'form-control select2', 'id' => 'account_id', 'style' => 'width:100%;']) !!}
                            </div>
                        </div>
                    </div>
                @endif
                <div class="clearfix"></div>

                @include('transaction_payment.payment_type_details')
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('note', __('lang_v1.payment_note') . ':') !!}
                        {!! Form::textarea('note', $payment_line->note, ['class' => 'form-control', 'rows' => 3]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
