@extends('layouts.app')
@section('title', __('business_partner.transactions'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'business_partner.transactions' )
            <small>@lang( 'business_partner.transactions' )</small>
        </h1>
        <!-- <ol class="breadcrumb">
                                                                                                                                                            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                                                                                                                                                            <li class="active">Here</li>
                                                                                                                                                        </ol> -->

    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['class' => 'box-primary', 'title' => __('business_partner.transaction')])
            <div class="row">
                <div class="col-md-3">
                    <table class="table table-sm">
                        <tr>
                            <td>@lang('business_partner.name')</td>
                            <td>{{ $business_partners->name }}</td>
                        </tr>
                        <tr>
                            <td>@lang('business_partner.mobile')</td>
                            <td>{{ $business_partners->mobile }}</td>
                        </tr>
                        <tr>
                            <td>@lang('business_partner.address')</td>
                            <td>{{ $business_partners->address }}</td>
                        </tr>
                        <tr>
                            <td>@lang('business_partner.balance')</td>
                            <td>    <input name="address" id="balance" type="text" class="form-control" value="{{ $final_amount }}" readonly></td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-3">
                  
                </div>
            </div>

            <form action="{{ action('BusinessPartnerController@UpdatePartner') }}" method="post" style="display: none;"> @csrf
                <input id="Partner_id" name="id" type="text" value="{{ $business_partners->id }}" link="{{ action('BusinessPartnerController@GetPaymentsData') }}" hidden>
                <div class="row">
                    <div class="form-group col-md-3">
                        <label for="test">@lang('business_partner.name')</label>
                        <input name="name" id="test" type="text" class="form-control" value="{{ $business_partners->name }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="test">@lang('business_partner.mobile')</label>
                        <input name="mobile" id="test" type="text" class="form-control" value="{{ $business_partners->mobile }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="test">@lang('business_partner.address')</label>
                        <input name="address" id="test" type="text" class="form-control" value="{{ $business_partners->address }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="test">@lang('business_partner.balance')</label>
                    

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
                    <a href="{{ action('TransactionPaymentController@getPayContactDue_Partner', [0]) }}?owner={{ $business_partners->id }}&partner_id={{ $business_partners->id }}" class="pay_sale_due" style="color: aliceblue;"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>@lang('business_partner.add')</a>

                </div>

                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="Business_partner_details" style="text-align:center;">
                            <thead>
                                <tr>
                                    <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                                    <th>#</th>
                                    {{-- <th>Note</th> --}}
                                    <th>@lang('business_partner.account')</th>
                                    <th>@lang('business_partner.method')</th>
                                    <th>@lang('business_partner.type')</th>
                                    <th>@lang('business_partner.debit')</th>
                                    <th>@lang('business_partner.credit')</th>
                                    <th>@lang('business_partner.balance')</th>
                                    <th>@lang('business_partner.created_by')</th>
                                    <th>@lang('business_partner.created_at')</th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                              
                                    <td>-</td>
                                    <td>-</td>
                                    <td>رصيد افتتاحي</td>
                                    <?php
                                    if ($business_partner->type == "credit") {
                                        echo "<td>" . $business_partner->open_balance . "</td>";
                                    }else{
                                        echo "<td></td>";
                                    }
                                    if ($business_partner->type == "debit") {
                                        echo "<td>" . $business_partner->open_balance . "</td>";
                                    }else{
                                        echo "<td></td>";
                                    }
                                    ?>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>{{$business_partner->created_at}}</td>
                                </tr>
                            </thead>
                         
                            <!-- business_partner -->
                        </table>
                        <div class="box-footer">
            <button type="button" class="btn btn-primary no-print pull-right"onclick="window.print()">
          <i class="fa fa-print"></i> @lang('messages.print')</button>
        </div>
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
                    $.ajax({
                        method: 'POST',
                        url: $(this).attr('link'),
                        dataType: 'json',
                        // headers: {
                        //     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        // },
                        data: {
                            "payment_id": $(this).attr('payment_id'),
                            "owner": $('#Partner_id').val(),
                        },
                        success: function(result) {
                            toastr.success(result.msg);
                            toastr.error(result.msg);

                            $('#balance').val(result.final_amount);

                            Business_partner_details.ajax.reload();
                            // Business_partner_Credit.ajax.reload();
                        },
                    });
                } else {
                    //    alert("Nok");
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
                    data: 'balance',
                    name: 'balance'
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
