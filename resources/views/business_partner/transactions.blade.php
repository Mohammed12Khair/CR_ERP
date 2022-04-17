@extends('layouts.app')
@section('title', __('business.business_locations'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'business.business_locations' )
            <small>@lang( 'business.manage_your_business_locations' )</small>
        </h1>
        <!-- <ol class="breadcrumb">
                                                        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                                                        <li class="active">Here</li>
                                                    </ol> -->
        <a href="<?php echo action('BusinessPartnerTransactionController@createTransaction', [$business_partners->id]); ?>">Add</a>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('business.all_your_business_locations')])
            <form action="{{ action('BusinessPartnerController@UpdatePartner') }}" method="post"> @csrf
                <input id="Partner_id" name="id" type="text" class="form-control" value="{{ $business_partners->id }}">
                <div class="row">
                    <div class="form-group col-md-3">
                        <label for="test">name</label>
                        <input name="name" id="test" type="text" class="form-control" value="{{ $business_partners->name }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="test">Mobile</label>
                        <input name="mobile" id="test" type="text" class="form-control" value="{{ $business_partners->mobile }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="test">Address</label>
                        <input name="address" id="test" type="text" class="form-control" value="{{ $business_partners->address }}">
                    </div>
                </div>
                <div class="row">
                    {{-- <button name="action" value="update" type="submit" class="btn btn-primary btn-sm">Save</button> --}}
                </div>
            </form>
        @endcomponent

        <div class="row">
            <div class="col-sm-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">السلفيات</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="Business_partner_Credit">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>name</th>
                                        <th>mobile</th>
                                        <th>address</th>
                                        <th>created_by</th>
                                        <th>created_at</th>
                                        <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
            </div>
            <div class="col-sm-6">
                <div class="box box-primary">
                    <div class="box-header">

                        <h3 class="box-title"> العهد</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="Business_partner_debit">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>name</th>
                                        <th>mobile</th>
                                        <th>address</th>
                                        <th>created_by</th>
                                        <th>created_at</th>
                                        <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
@endsection


@section('javascript')
    <script>
        Business_partner_Credit = $('#Business_partner_Credit').DataTable({
            processing: true,
            serverSide: true,
            ajax: base_path + '/BusinessPartner/getCredit/' + $('#Partner_id').val(),
            columnDefs: [{
                orderable: false,
                searchable: false,
            }, ],
            aaSorting: [1, 'asc'],
            columns: [{
                    data: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'created_by',
                    name: 'created_by'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ],
        });
        Business_partner_debit = $('#Business_partner_debit').DataTable({
            processing: true,
            serverSide: true,
            ajax: base_path + '/BusinessPartner/getDebit/' + $('#Partner_id').val(),
            columnDefs: [{
                orderable: false,
                searchable: false,
            }, ],
            aaSorting: [1, 'asc'],
            columns: [{
                    data: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'created_by',
                    name: 'created_by'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action'
                },
            ],
        });
    </script>
@endsection
