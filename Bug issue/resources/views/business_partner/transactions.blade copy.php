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
                <input id="Partner_id" name="id" type="text" value="{{ $business_partners->id }}" link="{{action('BusinessPartnerController@GetPaymentsData')}}"  hidden>
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
                        <label id="Type_pa">Type</label>
                        <select id="Type_pay" class="form-control">
                            <option value="credit">credit</option>
                            <option value="debit">debit</option>
                        </select>
                        {{-- <input class="form-control" id="amount" name="amount" type="number"> --}}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label id="Type_pa">Account</label>
                        <select id="account_id" class="form-control">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
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
                        <h3 class="box-title">السلفيات</h3>
                        <strong id="debit_amount" style="float: left;color:rgb(0, 255, 13);">999</strong>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="Business_partner_Credit">
                                <thead>
                                    <tr>
                                        <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                                        <th>#</th>
                                        {{-- <th>Note</th> --}}
                                        <th>Type</th>
                                        <th>Open Amount</th>
                                        <th>created_by</th>
                                        <th>created_at</th>
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
                        <strong id="credit_amount" style="float: left;color:red;">999</strong>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="Business_partner_debit">
                                <thead>
                                    <tr>
                                        <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                                        <th>#</th>
                                        {{-- <th>Note</th> --}}
                                        <th>Type</th>
                                        <th>Open Amount</th>
                                        <th>created_by</th>
                                        <th>created_at</th>
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
        $(document).on('click', '.delete_transaction', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    alert("OK");
                    $.ajax({
                        method: 'POST',
                        url: $(this).attr('link'),
                        dataType: 'json',
                        // headers: {
                        //     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        // },
                        data: {
                            "payment_id": $(this).attr('payment_id'),
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


        Business_partner_Credit = $('#Business_partner_Credit').DataTable({
            processing: true,
            serverSide: true,
            ajax: base_path + '/BusinessPartner/getCredit/' + $('#Partner_id').val(),
            columnDefs: [{
                orderable: false,
                searchable: false,
            }, ],
            aaSorting: [1, 'desc'],
            columns: [{
                    data: 'action',
                    name: 'action'
                }, {
                    data: 'id'
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
            aaSorting: [1, 'desc'],
            columns: [{
                    data: 'action',
                    name: 'action'
                }, {
                    data: 'id'
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
                    alert($('#account_id').val());
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
                            "account_id": $('#account_id').val(),
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


        function UpdateBalance() {
            alert("UpdateBalance");
            alert($('#Partner_id').attr('link'));
            alert($('#Partner_id').val());
            var partner_id = $('#Partner_id').val();
            $.ajax({
                method: 'POST',
                url: $('#Partner_id').attr('link'),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    "owner": $('#Partner_id').val(),
                },
                success: function(result) {
                    toastr.success(result.msg);
                    toastr.error(result.msg);

                    Business_partner_debit.ajax.reload();
                    Business_partner_Credit.ajax.reload();
                },
            });

        }

        // UpdateBalance();
    </script>
@endsection
