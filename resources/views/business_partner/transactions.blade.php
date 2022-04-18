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

    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('business.all_your_business_locations')])
            <form action="{{ action('BusinessPartnerController@UpdatePartner') }}" method="post"> @csrf
                <input id="Partner_id" name="id" type="text" value="{{ $business_partners->id }}" hidden>
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
        @component('components.filters', ['title' => __('report.filters')])
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label id="amount">Amount</label>
                        <input class="form-control" id="amount_pay" name="amount" type="number">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label id="note">note</label>
                        <input class="form-control" id="note_pay" name="note" type="text">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label id="Type_pa">Type</label>
                        <select id="Type_pay" class="form-control">
                            <option value="credit">credit</option>
                            <option value="debit">debit</option>
                        </select>
                        {{-- <input class="form-control" id="amount" name="amount" type="number"> --}}
                    </div>
                </div>
            </div>
            <div class="row">
                <button class="btn btn-sm  Add_transaction" link="{{ action('BusinessPartnerTransactionController@createTransaction') }}">Save</button>
            </div>
        @endcomponent

        <div class="row">

            <div class="col-sm-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"> السلفيات</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="Business_partner_Credit">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Note</th>
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

                        <h3 class="box-title">
                            العهد</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="Business_partner_debit">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Note</th>
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


    <div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

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
                    data: 'note',
                    name: 'note'
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
                    data: 'note',
                    name: 'note'
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


        $(document).on('click', '.Add_transaction', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    alert("OK");
                    alert($('#amount_pay').val());
                    alert($('#note_pay').val());
                    alert($('#Type_pay').val());
                    alert($('#Partner_id').val());
                    $.ajax({
                        method: 'POST',
                        url: $(this).attr('link'),
                        dataType: 'json',
                        // headers: {
                        //     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        // },
                        data: {
                            "amount": $('#amount_pay').val(),
                            "note": $('#note_pay').val(),
                            "type": $('#Type_pay').val(),
                            "owner": $('#Partner_id').val(),
                        },
                        success: function(result) {
                            toastr.success(result.msg);
                            toastr.error(result.msg);

                            Business_partner_debit.ajax.reload();
                            Business_partner_Credit.ajax.reload();
                        },
                    });
                } else {
                    alert("Nok");
                }
            });
        });
    </script>
@endsection
