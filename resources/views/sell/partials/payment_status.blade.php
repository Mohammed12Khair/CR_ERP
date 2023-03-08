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


<?php
$ApprovalStatus=0;
$ApprovalStatusData=DB::select(DB::raw('SELECT * FROM transactions_approved WHERE status = 1 and transaction_id=:id'),["id"=>$id]);
foreach($ApprovalStatusData as $ApprovalStatusDatas){
    $ApprovalStatus=1;
}

if($ApprovalStatus > 0){
    $result_out="Need Approved";
   
}else{
    $result_out="Approved";
}

?>
<br>
<!-- <a href="{{ action('TransactionPaymentController@showDelivery', [$id])}}" class="payment-status-label"> -->
<!-- <a href="#">data</a> -->
<a href="{{ action('TransactionPaymentController@Approved', [$id])}}"  >
<span class="label bg-light-green">{{$result_out}}
    </span></a>
 