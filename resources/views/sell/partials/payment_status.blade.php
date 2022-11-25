<a href="{{ action('TransactionPaymentController@show', [$id])}}" class="view_payment_modal payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__('lang_v1.' . $payment_status)}}">
    <span class="label @payment_status($payment_status)">{{__('lang_v1.' . $payment_status)}}
    </span></a>
    <br>
    <br>
<a href="{{ action('TransactionPaymentController@showDelivery', [$id])}}" class="view_Delivery_modal payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__('lang_v1.' . $payment_status)}}">
<span class="label bg-blue">التسليم
    </span></a>