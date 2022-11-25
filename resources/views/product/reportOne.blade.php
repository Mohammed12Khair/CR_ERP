@extends('layouts.app')
@section('title', __('sale.products'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('sale.products')
            <small>@lang('lang_v1.manage_products')</small>
        </h1>
        <!-- <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
    <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                         @include('report.partials.stock_report_transfer_table')
                @endcomponent
            </div>
        </div>

    </section>
    <!-- /.content -->
@endsection