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
        @component('components.filters', ['class' => 'box-primary', 'title' => __('business.all_your_business_locations') ])
            <form action="{{ action('BusinessPartnerController@UpdatePartner') }}" method="post"> @csrf
                <input id="Partner_id" name="id" type="text" value="{{ $business_partners->id }}" link="{{ action('BusinessPartnerController@GetPaymentsData') }}" hidden>
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
                    <div class="form-group col-md-3">
                        <h4>{{ $final_amount }}</h4>
                    </div>
                </div>
                <div class="row">
                    {{-- <button name="action" value="update" type="submit" class="btn btn-primary btn-sm">Save</button> --}}
                </div>

            </form>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary'])
            <div>
                <div class="btn btn-info">
                    <a href="{{ action('TransactionPaymentController@getPayContactDue_Partner', [0]) }}?owner={{ $business_partners->id }}&partner_id={{ $business_partners->id }}" class="pay_sale_due" style="color: aliceblue;"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>ADD</a>
                    
                </div>
                <h3 style="float: left;">{{ $final_amount }}</h3>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="Business_partner_details">
                            <thead>
                                <tr>
                                    <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                                    <th>#</th>
                                    {{-- <th>Note</th> --}}
                                    <th>account</th>
                                    <th>method</th>
                                    <th>Type</th>
                                    <th>debit</th>
                                    <th>credit</th>
                                    <th>created_by</th>
                                    <th>created_at</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @endcomponent
    </section>

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


        Business_partner_details = $('#Business_partner_details').DataTable({
            processing: true,
            serverSide: true,
            ajax: base_path + '/BusinessPartner/Business_partner_details/' + $('#Partner_id').val(),
            columnDefs: [{
                orderable: false,
                searchable: false,
            }, ],
            aaSorting: [1, 'asc'],
            columns: [{
                    data: 'action',
                    name: 'action'
                }, {
                    data: 'id'
                },
                {
                    data: 'account',
                    name: 'account'
                },
                {
                    data: 'method',
                    name: 'method'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'amount',
                    name: 'amount'
                }, {
                    data: 'amount_less',
                    name: 'amount_less'
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
