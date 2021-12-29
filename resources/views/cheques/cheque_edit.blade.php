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
    <div class="box-tools">
        <!-- <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{action('UnitController@create')}}" data-container=".unit_modal">
            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button> -->
    </div>
    @endslot
    @endcan
    @can('cheque.view')
    <div class="table-responsive">
        <form action="<?php echo action('bankcheques@EditChequeSave'); ?>" method="POST">
        @csrf 
            @foreach($cheque_payments as $cheque_payment)
            <input name="id" value="{{$cheque_payment->id}}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label("cheque_number",__('lang_v1.cheque_no')) !!}
                        {!! Form::text("cheque_number", $cheque_payment->cheque_number, ['class' => 'form-control', 'placeholder' => __('lang_v1.cheque_no'),'required']); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label("cheque_date",__('lang_v1.cheque_date')) !!}
                        {!! Form::date("cheque_date", $cheque_payment->cheque_date, ['class' => 'form-control', 'placeholder' => __('lang_v1.cheque_date'),'required']); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <button type="submit" class="btn btn-md btn-success">Save</button>
                </div>
            </div>
            @endforeach
        </form>
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