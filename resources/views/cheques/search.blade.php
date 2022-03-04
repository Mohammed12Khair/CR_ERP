@extends('layouts.app')
@section('title', __('cheque.units'))
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
        @component('components.filters', ['class' => 'box-primary'])
            <form method="get" action="<?php echo action('bankcheques@GetAdvanceSearch'); ?>">
                @csrf 
                <div class="row">
                    <div style="margin-top:1%;" class="col-md-3">
                        <select class="form-control" name="client">
                            <option value="">@lang( 'cheque.client_name' )</option>
                            @foreach ($client_data as $client)
                                <option value="{{ $client }}">{{ $client }}</option>
                            @endforeach
                        </select>
                        {{-- <input type="text" class="form-control" placeholder="@lang( 'cheque.client_name' )"> --}}
                    </div>
                    <div style="margin-top:1%;" class="col-md-3" >
                        <input name="cheque_number" value="" type="text" class="form-control" placeholder="@lang( 'cheque.cheque_number' )">
                    </div>
                    <div  style="margin-top:1%;" class="col-md-2">
                        <select class="form-control" name="transaction_type">
                            <option value="">@lang( 'cheque.transaction_type' )</option>
                            @foreach ($transaction_tp as $transaction)
                                <option value="{{ $transaction }}">{{ $transaction }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="margin-top:1%;" class="col-md-2">
                        <select class="form-control" name="status">
                            <option value="All">@lang( 'cheque.status' )</option>
                            @foreach ($status as $statu)
                                <option value="{{ $statu }}">{{ $statu }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-md btn-primary">@lang("cheque.filter")</button>
                    </div>
                  
                </div>
            </form>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary', 'title' => __('cheque.all_your_units')])
            @can('cheque.create')
                @slot('tool')
                @endslot
            @endcan
            @can('cheque.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="cheque_table" style="text-align: center;">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>@lang( 'cheque.client_name' )</th>
                                <th>@lang( 'cheque.transaction_id' )</th>
                                <th>@lang( 'cheque.cheque_number' )</th>
                                <th>@lang( 'cheque.cheque_date' )</th>
                                <th>@lang( 'cheque.transaction_type' )</th>
                                <th>@lang( 'cheque.amount' )</th>
                                <th>@lang( 'cheque.username' )</th>
                                <th>@lang( 'cheque.created_at' )</th>
                                <th>@lang( 'cheque.status' )</th>
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
