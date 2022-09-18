@extends('layouts.app')
@section('title', 'delete')
@section('content')


<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Delete <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
    <form action="<?php echo action('PurchaseController@deleteaction'); ?>" method="POST">
    @csrf     <input name="transaction_id" value="{{$id}}" type="text" hidden>
        <input name="user_id" value="{{$user_id}}" type="text" hidden>
        <label for="x">Delete reasone</label>
        <textarea style="height: 250px;" id="x" maxlength="500" name="deletereasone" type="text" class="form-control" required></textarea>
        <br>
        <button type="submit" class="btn btn-success">save</button>
    </form>

</section>
<!-- Main content -->


@endsection

@section('javascript')
<script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        __page_leave_confirmation('#add_purchase_form');
        $('.paid_on').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true,
        });
    });
    $(document).on('change', '.payment_types_dropdown, #location_id', function(e) {
        var default_accounts = $('select#location_id').length ?
            $('select#location_id')
            .find(':selected')
            .data('default_payment_accounts') : [];
        var payment_types_dropdown = $('.payment_types_dropdown');
        var payment_type = payment_types_dropdown.val();
        var payment_row = payment_types_dropdown.closest('.payment_row');
        var row_index = payment_row.find('.payment_row_index').val();

        var account_dropdown = payment_row.find('select#account_' + row_index);
        if (payment_type && payment_type != 'advance') {
            var default_account = default_accounts && default_accounts[payment_type]['account'] ?
                default_accounts[payment_type]['account'] : '';
            if (account_dropdown.length && default_accounts) {
                account_dropdown.val(default_account);
                account_dropdown.change();
            }
        }

        if (payment_type == 'advance') {
            if (account_dropdown) {
                account_dropdown.prop('disabled', true);
                account_dropdown.closest('.form-group').addClass('hide');
            }
        } else {
            if (account_dropdown) {
                account_dropdown.prop('disabled', false);
                account_dropdown.closest('.form-group').removeClass('hide');
            }
        }
    });

    $('document').ready(function() {
        $("[data-toggle=collapse]").click();
    });


    var refund_enable = true;

    // Refund & purchase_quantityChange
    $(document).on('change', '.purchase_quantity', function(e) {
        // alert($(this).val());
        var total = 0;
        var quant = [];
        var price = [];
        $('.refund').each(function(i, obj) {
            price.push(parseFloat($(this).val()));
            // total = total + parseFloat($(this).val());
            //test
        });
        $('.purchase_quantity').each(function(i, obj) {
            quant.push(parseFloat($(this).val()));
            // total = total + parseFloat($(this).val());
            //test
        });
        for (let i = 0; i < price.length; i++) {
            total = parseFloat(price[i] * quant[i]);
        }
        $('.refund_total').val(total);
    });

    // Refund & purchase_quantityChange
    $(document).on('change', '.refund', function(e) {
        // alert($(this).val());
        var total = 0;
        var quant = [];
        var price = [];
        $('.refund').each(function(i, obj) {
            price.push(parseFloat($(this).val()));
            // total = total + parseFloat($(this).val());
            //test
        });
        $('.purchase_quantity').each(function(i, obj) {
            quant.push(parseFloat($(this).val()));
            // total = total + parseFloat($(this).val());
            //test
        });
        for (let i = 0; i < price.length; i++) {
            total = parseFloat(price[i] * quant[i]);
        }
        $('.refund_total').val(total);
    });




    // Refund Change
    $(document).on('click', '#refund_enable', function(e) {
        if (refund_enable) {
            $('.refund').each(function(i, obj) {
                $(this).val(0);
                $(this).prop("readonly", true);
            });
            $('.refund_total').val(0);
            $('.refund_total').prop("readonly", true);
            // $(this).prop("readonly", true);
            refund_enable = false;
        } else {
            $(this).prop("readonly", false);
            $('.refund_total').prop("readonly", false);
            refund_enable = true;
        }
    });
</script>
@include('purchase.partials.keyboard_shortcuts')
@endsection