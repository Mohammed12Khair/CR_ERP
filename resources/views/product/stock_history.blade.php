@extends('layouts.app')
@section('title', __('lang_v1.product_stock_history'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.product_stock_history')</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['title'=>""])
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
                    {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                </div>
            </div>

            <div class="col-md-3">
            <div class="form-group">
                    <label for="product_ID">@lang('product.variations'):</label>
                    <select class="select2 form-control" name="product_ID" id="product_ID">
                    @foreach($product_all as $product_data)   
                    <option value="{{$product_data->id}}">{{$product_data->name}}</option>
                    @endforeach
                    </select>
                </div>
                </div>

            @if($product->type == 'variable')
            <div class="col-md-3">
                <div class="form-group">
                    <label for="variation_id">@lang('product.variations'):</label>
                    <select class="select2 form-control" name="variation_id" id="variation_id">
                        @foreach($product->variations as $variation)
                        <option value="{{$variation->id}}">{{$variation->product_variation->name}} - {{$variation->name}} ({{$variation->sub_sku}})</option>
                        @endforeach
                    </select>
                </div>
          
            </div>
            @else
            <input type="hidden" id="variation_id" name="variation_id" value="{{$product->variations->first()->id}}">
            @endif
            @endcomponent
            @component('components.widget')
            <div id="product_stock_history" style="display: none;"></div>
            @endcomponent
        </div>
    </div>

</section>
<!-- /.content -->
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        load_stock_history($('#variation_id').val(), $('#location_id').val(),1);
    });

    function load_stock_history(variation_id, location_id,product_ID) {
        //alert(product_ID);
        $('#product_stock_history').fadeOut();
        $.ajax({
            url: '/products/stock-history/' + variation_id + "?location_id=" + location_id + "&product_ID=" + product_ID,
            dataType: 'html',
            success: function(result) {
                $('#product_stock_history')
                    .html(result)
                    .fadeIn();

                __currency_convert_recursively($('#product_stock_history'));

                $('#stock_history_table').DataTable({
                    searching: false,
                    ordering: false
                });
            },
        });
    }

    $(document).on('change', '#variation_id, #location_id,#product_ID', function() {
        load_stock_history($('#variation_id').val(), $('#location_id').val(),$('#product_ID').val());
    });


    $(document).ready(function() {
        $('#stock_history_table').DataTable();
    });
</script>
@endsection