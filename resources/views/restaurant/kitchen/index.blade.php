@extends('layouts.restaurant')
@section('title', __( 'restaurant.kitchen' ))

@section('content')
<!-- Main content -->
<section class="content min-height-90hv no-print">

    <div class="row">
        <div class="col-md-12 text-center">
            <h3>@lang( 'restaurant.all_orders' ) - @lang( 'restaurant.kitchen' ) @show_tooltip(__('lang_v1.tooltip_kitchen'))</h3>
        </div>
    </div>

    <div class="box">
        <div class="box-header">
            <button type="button" class="btn btn-sm btn-primary pull-right" id="refresh_orders"><i class="fas fa-sync"></i> @lang( 'restaurant.refresh' )</button>
        </div>
        <div class="box-body">
            <input type="hidden" id="orders_for" value="kitchen">
            <div class="row">
                <div class="col-md-6">
                    <h1 style="text-align:center;"> الطلبات الجديدة</h1>
                    <div id="orders_div" style="border:1px solid grey;">
                        @include('restaurant.partials.show_orders', array('orders_for' => 'kitchen'))
                    </div>
                </div>
                <div class="col-md-6">
                    <h1 style="text-align:center;">تحت التجهيز</h1>
                    <div style="background-color: grey;border:1px solid grey;"  id="orders_div_old">
                        @include('restaurant.partials.show_orders_ongoing', array('orders_for' => 'kitchen'))
                    </div>
                </div>
            </div>
        </div>
        <div class="overlay hide">
            <i class="fas fa-sync fa-spin"></i>
        </div>
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a.mark_as_cooked_btn', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                icon: "info",
                buttons: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var _this = $(this);
                    var href = _this.data('href');
                    $.ajax({
                        method: "GET",
                        url: href,
                        dataType: "json",
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                _this.closest('.order_div').remove();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection