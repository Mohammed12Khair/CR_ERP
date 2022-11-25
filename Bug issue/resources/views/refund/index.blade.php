@extends('layouts.app')
@section('title', 'Refund')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <small></small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
    @can('brand.create')
    @slot('tool')
    <!-- <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                        data-href="{{action('BrandController@create')}}" 
                        data-container=".brands_modal">
                        <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div> -->
    @endslot
    @endcan
    @can('brand.view')
    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center" id="refund_table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Refund</th>
                    <th>Transaction</th>
                    <th>Status</th>
                    <th>Ref number</th>
                    <th>Created_at</th>
                    <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($refund as $refund_line)
                <tr>
                    <td><?php
                        echo App\Contact::where('id', $refund_line->contact_id)->first()->name;
                        ?></td>
                    <td>{{$refund_line->refund_total}}</td>
                    <td>{{$refund_line->transaction_id}}</td>
                    <td>
                        @if($refund_line->status == 0)
                        <span class="label bg-yellow">مستحق الدفع
                        </span>
                        @elseif($refund_line->status == 1)
                        <span class="label bg-light-green">مدفوع
                        </span>
                        @endif
                    </td>
                    <td><?php
                        echo App\Transaction::where('id', $refund_line->transaction_id)->first()->ref_no . '<br>' . __('lang_v1.' . App\Transaction::where('id', $refund_line->transaction_id)->first()->status);
                        ?></td>
                    <td>{{$refund_line->created_at}}</td>
                    <td>
                        @if($refund_line->status == 0)
                        <a href="<?php echo action('RefundController@updateBalanceAdd', [$refund_line->id]); ?>" class="btn btn-sm btn-success"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>الدفع</a>
                        @else
                        <a href="<?php echo action('RefundController@updateBalanceRemove', [$refund_line->id]); ?>" class="btn btn-sm btn-danger"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>قبض</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endcan
    @endcomponent

    <div class="modal fade brands_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection


@section('javascript')
<script>
    $(document).ready(function() {
        $('#refund_table').DataTable({
            buttons: [
                'copy', 'excel', 'pdf'
            ]
        });
    });
</script>
@endsection