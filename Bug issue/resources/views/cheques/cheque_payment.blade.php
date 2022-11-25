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
                    <table class="table table-bordered table-striped" id="cheque_tablex">
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
                            @foreach ($cheque_payments as $cheque_payment)
                                <tr>
                                    <td>{{ $cheque_payment->id }}</td>
                                    <td>{{ $cheque_payment->amount }}</td>
                                    <td><?php echo get_account_name($cheque_payment->account_id); ?></td>
                                    <td>{{ $cheque_payment->created_at }}</td>
                                    <td>
                                        @can('cheque.edit')
                                            <a class="btn btn-xs btn-danger delete_payment" data-href="<?php echo action('TransactionPaymentController@destroy', [$cheque_payment->id]); ?>">Delete</a>
                                        </td>
                                    @endcan
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
        // Start : CRUD for cheques 
        //cheques table
        var cheque_table = $('#cheque_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/cheques',
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
                    data: 'payment_id',
                    name: 'payment_id'
                },
                {
                    data: 'transaction_id',
                    name: 'transaction_id'
                },
                {
                    data: 'cheque_number',
                    name: 'cheque_number'
                },
                {
                    data: 'cheque_date',
                    name: 'cheque_date'
                },
                {
                    data: 'transaction_type',
                    name: 'transaction_type'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'username',
                    name: 'username'
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
                        method: 'delete',
                        dataType: 'json',
                        success: function(result) {
                            if (result.success === true) {
                                $('div.payment_modal').modal('hide');
                                $('div.edit_payment_modal').modal('hide');
                                toastr.success(result.msg);
                                location.reload();
                                if (typeof purchase_table != 'undefined') {
                                    purchase_table.ajax.reload();
                                }
                                if (typeof sell_table != 'undefined') {
                                    sell_table.ajax.reload();
                                }
                                if (typeof expense_table != 'undefined') {
                                    expense_table.ajax.reload();
                                }
                                if (typeof ob_payment_table != 'undefined') {
                                    ob_payment_table.ajax.reload();
                                }
                                if (typeof cheque_table != 'undefined') {
                                    cheque_table.ajax.reload();
                                }
                                // project Module
                                if (typeof project_invoice_datatable != 'undefined') {
                                    project_invoice_datatable.ajax.reload();
                                }

                                if ($('#contact_payments_table').length) {
                                    get_contact_payments();
                                }
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
    </script>
@endsection
