@extends('layouts.app')
@section('title', 'edit')
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>تفاصيل التعديل<i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('purchase.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h1>
    <table class="table table-bordered table-striped ajax_view" id="edit_table">
        <thead>
            <tr>
                <!-- <th>@lang('messages.action')</th> -->
                <!-- <th><img src="{{ asset('img/gear.gif') }}" width="25"></th> -->
                <th>@lang('messages.date')</th>
                <th>سعر الشراء</th>
                <th>اسم المنتج</th>
                <!-- <th>@lang('lang_v1.contact_no')</th> -->
                <th>الكميه</th>
                <!-- <th>@lang('sale.location')</th> -->
                <!-- <th>نوع الفاتوره</th> -->
                <!-- <th>@lang('lang_v1.payment_method')</th>
                <th>@lang('sale.total_paid')</th>
                <th>@lang('lang_v1.sell_due')</th>
                <th>@lang('lang_v1.sell_return_due')</th>
                <th>@lang('lang_v1.shipping_status')</th>
                <th>@lang('lang_v1.total_items')</th>
                <th>@lang('lang_v1.types_of_service')</th>
                <th>{{ $custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1' )}}</th>
                <th>@lang('lang_v1.added_by')</th>
                <th>@lang('sale.sell_note')</th>
                <th>@lang('sale.staff_note')</th>
                <th>@lang('sale.shipping_details')</th>
                <th>@lang('restaurant.table')</th>
                <th>@lang('restaurant.service_staff')</th> -->
            </tr>
        </thead>
        <tbody>
            @foreach($data as $onedata)
            <tr>
                @if($type == 'purchase')
                <td>{{$onedata->updated_at}}</td>
                <td>{{$onedata->purchase_price}}</td>
                <td><?php echo App\Product::where('id', $onedata->product_id)->first()->name; ?></td>
                <td>{{$onedata->quantity}}</td>
                <!-- <td></td> -->
                <!-- <td></td> -->
                @endif
                @if($type == 'sell')
                <td>{{$onedata->updated_at}}</td>
                <td>{{$onedata->unit_price}}</td>
                <td><?php echo App\Product::where('id', $onedata->product_id)->first()->name; ?></td>
                <td>{{$onedata->quantity}}</td>
                <!-- <td></td> -->
                <!-- <td></td> -->
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</section>
<!-- Main content -->
@endsection

@section('javascript')
<script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#edit_table').DataTable();
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