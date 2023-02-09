<a href="{{ action('TransactionPaymentController@show', [$id])}}" class="view_payment_modal payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__('lang_v1.' . $payment_status)}}">
    <span class="label @payment_status($payment_status)">{{__('lang_v1.' . $payment_status)}}
    </span></a>
    <br>
<?php
$result=0;
$Remain=DB::select(DB::raw('SELECT * FROM transaction_sell_lines_delivery WHERE quantity <> delivery and transaction_id=:id'),["id"=>$id]);
foreach($Remain as $Remains){
    $result=1;
}

if($result == 0){
    $word="تم التسليم";
    $colour="bg-green";
}else{ 
    $word=" مستحق التسليم";
    $colour="bg-red";
}

// echo $id;


?>
<a href="{{ action('TransactionPaymentController@showDelivery', [$id])}}" class="view_Delivery_modal payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__('lang_v1.' . $payment_status)}}">
<span class="label {{$colour}}">{{$word}}
    </span></a>