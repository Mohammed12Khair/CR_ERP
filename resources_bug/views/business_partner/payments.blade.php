@extends('layouts.app')
@section('title', __('cheque.units'))
@section('content')


    <?php
    function get_account_name($account_id)
    {
        try {
            $account = App\Account::where('id', $account_id)->first();
            return $account->name;
        } catch (Exception $e) {
            return 'None';
        }
    }
    ?>

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'cheque.units' )
            <input id="Tx" value="{{ $TransactionId }}">
            <small>@lang( 'cheque.manage_your_units' )</small>
        </h1>
        <!-- <ol class="breadcrumb">
                                            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                                            <li class="active">Here</li>
                                        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('cheque.all_your_units')])
            @can('cheque.create')
                @slot('tool')
                    <div class="box-tools">
                        <!-- <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{ action('UnitController@create') }}" data-container=".unit_modal">
                                                                                                                                                            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button> -->
                    </div>
                @endslot
            @endcan
            @can('cheque.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="BusinessPartnerPayment_table">
                        <thead>
                            <tr>
                                <th>@lang( 'cheque.payment_id' )</th>
                                <th>@lang( 'cheque.amount' )</th>
                                <th>@lang( 'cheque.account_id' )</th>

                                <th>Account</th>
                                <th>@lang( 'cheque.created_at' )</th>
                                <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center;">
                            @foreach ($Account as $Account_data)
                                <tr>
                                    <td>{{ $Account_data->id }}</td>
                                    <td>{{ $Account_data->amount }}</td>
                                    <td>{{ $Account_data->account_id }}</td>
                                    <td>{{ $Account_data->created_by }}</td>
                                    <td>{{ $Account_data->created_at }}</td>
                                    <td><a class="btn btn-xs btn-danger delete_payment" parent_id="{{ $Account_data->id }}" transaction_id="{{ $Account_data->transaction_payment_id }}" data-href="{{ action('TransactionPaymentController@destroy_partner_payment') }}">Delete</a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>

                    </table>
                </div>
            @endcan
        @endcomponent


        <!-- Model for the payments -->
    </section>

    <!-- /.content -->

@endsection
@section('javascript')
    <script>
        $(document).on('click', '.delete_payment', function(e) {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_payment,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    $.ajax({
                        url: $(this).data('href'),
                        method: 'post',
                        dataType: 'json',
                        data: {
                            "id": $(this).attr('transaction_id'),
                            "parent_id": $(this).attr('parent_id')
                        },
                        success: function(result) {
                            BusinessPartnerPayment_table.ajax.reload();
                            if (result.success === true) {
                                toastr.success(result.msg);
                                window.location = window.location;
                            } else {
                                toastr.error(result.msg);
                            }
                            // BusinessPartnerPayment_table.ajax.reload();
                        },
                    });
                }
            });
        });




        BusinessPartnerPayment_table = $('#BusinessPartnerPayment_xtable').DataTable({
            processing: true,
            serverSide: true,
            ajax: base_path + 'BusinessPartner/showPayments/' + $("#Tx").val(),
            columnDefs: [{
                orderable: false,
                searchable: false,
            }, ],
            aaSorting: [1, 'desc'],
            columns: [{
                    data: 'action',
                    name: 'action'
                }
                    data: 'id'
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
    </script>
@endsection
