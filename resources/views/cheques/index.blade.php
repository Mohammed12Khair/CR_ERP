@extends('layouts.app')
@section('title', __( 'cheque.units' ))

@section('content')




<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'cheque.units' )
        <small>@lang( 'cheque.manage_your_units' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'cheque.all_your_units' )])
    @can('cheque.create')
    @slot('tool')
    <div class="box-tools">
        <!-- <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{action('UnitController@create')}}" data-container=".unit_modal">
            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button> -->
    </div>
    @endslot
    @endcan
    @can('cheque.view')
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="cheque_table">
            <thead>
                <tr>
                    <th>id</th>
                    <th>@lang( 'cheque.payment_id' )</th>
                    <th>@lang( 'cheque.transaction_id' )</th>
                    <th>@lang( 'cheque.cheque_number' )</th>
                    <th>@lang( 'cheque.cheque_date' )</th>
                    <th>@lang( 'cheque.transaction_type' )</th>
                    <th>@lang( 'cheque.amount' )</th>
                    <th>@lang( 'cheque.username' )</th>
                    <th>@lang( 'cheque.created_at' )</th>
                    <th>Status</th>
                    <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                </tr>
            </thead>

        </table>
    </div>
    @endcan
    @endcomponent


    <!-- Model for the payments -->
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>

<!-- /.content -->

@endsection
@section('javascript')
<script src="{{ asset('js/cheque.js') }}"></script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>

@endsection