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
                                <th>@lang( 'cheque.created_at' )</th>
                                <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center;">

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
        // Start : CRUD for cheques 
        //cheques table
        var BusinessPartnerPayment_table = $('#BusinessPartnerPayment_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: 'BusinessPartner/showPayments/' + {{ $TransactionId }},
            columnDefs: [{
                // targets: 3,
                orderable: false,
                searchable: false,
            }, ],
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'account',
                    name: 'account'
                },
                {
                    data: 'account_id',
                    name: 'account_id'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ],
        });
    </script>
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

                            } else {
                                toastr.error(result.msg);
                            }
                            // BusinessPartnerPayment_table.ajax.reload();
                        },
                    });
                }
            });
        });
    </script>
@endsection
