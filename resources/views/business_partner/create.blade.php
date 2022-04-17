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
                        <label for="test">name</label>
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
                </div>
                <div class="row">
                    <button name="action" value="update" type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        @endcomponent

    </section>
    <!-- /.content -->

@endsection
