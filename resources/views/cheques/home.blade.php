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
    <!-- <div class="box-tools">
        <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{action('UnitController@create')}}" data-container=".unit_modal">
            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
    </div> -->
    @endslot
    @endcan
    @can('cheque.view')
   
    <div class="row">
        @if(auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
        <div class="col-sm-6">
          @component('components.widget', ['class' => 'box-warning'])
          @slot('icon')
          <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          @endslot
          @slot('title')
          {{-- {{ __('lang_v1.sales_payment_dues') }} @show_tooltip(__('lang_v1.tooltip_sales_payment_dues')) --}}
          @endslot
          <table class="table table-bordered table-striped" id="" style="width: 100%;">
            <thead>
              <tr>
                <th>@lang( 'contact.customer' )</th>
                <th>@lang( 'sale.invoice_no' )</th>
                <th>@lang( 'home.due_amount' )</th>
                <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
              </tr>
            </thead>
          </table>
          @endcomponent
        </div>
        @endif
        @can('purchase.view')
        <div class="col-sm-6">
          @component('components.widget', ['class' => 'box-warning'])
          @slot('icon')
          <i class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i>
          @endslot
          @slot('title')
          {{-- {{ __('lang_v1.purchase_payment_dues') }} @show_tooltip(__('tooltip.payment_dues')) --}}
          @endslot
          <table class="table table-bordered table-striped" id="purchase_payment_dues_table" style="width: 100%;">
            <thead>
              <tr>
                <th>@lang( 'purchase.supplier' )</th>
                <th>@lang( 'purchase.ref_no' )</th>
                <th>@lang( 'home.due_amount' )</th>
                <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
              </tr>
            </thead>
          </table>
          @endcomponent
        </div>
        @endcan
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