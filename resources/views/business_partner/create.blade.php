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
        @component('components.widget', ['class' => 'box-primary', 'title' => __('business.all_your_business_locations')])
            <form action="{{ action('BusinessPartnerController@store') }}" method="post"> @csrf
                {{-- <input name="id" type="text" class="form-control"> --}}
                <div class="row">
                <div class="form-group col-md-3">
				{!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-user"></i>
					</span>
					{!! Form::select('contact_id', [], null, ['class' => 'form-control', 'placeholder' => __('messages.please_select'), 'required', 'id' => 'supplier_id']); !!}
					<span class="input-group-btn">
						<!-- <button type="button" class="btn btn-default bg-white btn-flat add_new_supplier" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button> -->
					</span>
				</div>
			</div>

                    <div class="form-group col-md-3">
                        <label for="test">Account Name</label>
                        <input name="name" id="test" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="test">Mobile</label>
                        <input name="mobile" id="test" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="test">Address</label>
                        <input name="address" id="test" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="test">Balance</label>
                        <select name="type" class="form-control">
                            <option value="None">none</option>
                            <option value="credit">credit</option>
                            <option value="debit">debit</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="test">open_balance</label>
                        <input name="open_balance" id="test" type="number" class="form-control" value="0">
                    </div>
                </div>
                <div class="row">
                    <button name="action" value="update" type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        @endcomponent

    </section>
    <!-- /.content -->

@endsection


@section('javascript')
<script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
@endsection


