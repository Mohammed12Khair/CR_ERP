@extends('layouts.app')
@section('title', __('business_partner.business_partner'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'business_partner.business_partner_all' )
            <small>@lang( 'business_partner.business_partner' )</small>
        </h1>
        <!-- <ol class="breadcrumb">
                                    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                                    <li class="active">Here</li>
                                </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('business_partner.business_partner_all')])
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-primary" href="{{ action('BusinessPartnerController@create') }}">
                        <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                    {{-- <button type="button" class="btn btn-block btn-primary btn-modal" data-href="{{ action('BusinessPartnerController@create') }}" data-container=".location_add_modal">
                        <i class="fa fa-plus"></i> @lang( 'messages.add' )</button> --}}
                </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="Business_partners">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('business_partner.name')</th>
                            <th>@lang('business_partner.mobile')</th>
                            <th>@lang('business_partner.address')</th>
                            <th>@lang('business_partner.created_by')</th>
                            <th>@lang('business_partner.created_at')</th>
                            <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade location_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade location_edit_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection


@section('javascript')
    <script>
        $(document).on('click', '.delete_partner', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    alert("OK");
                    $.ajax({
                        method: 'POST',
                        url: $(this).attr('href'),
                        dataType: 'json',
                        // headers: {
                        //     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        // },
                        data: {
                            "id": $(this).attr('row'),
                        },
                        success: function(result) {

                            toastr.success(result.msg);

                            toastr.error(result.msg);
                            Business_partners.ajax.reload();

                        },
                    });

                } else {
                    alert("Nok");
                }
            });
        });




        Business_partners = $('#Business_partners').DataTable({
            processing: true,
            serverSide: true,
            ajax: base_path + '/BusinessPartner',
            columnDefs: [{
                orderable: false,
                searchable: false,
            }, ],
            aaSorting: [1, 'asc'],
            columns: [{
                    data: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'mobile',
                    name: 'mobile'
                },
                {
                    data: 'address',
                    name: 'address'
                },
                {
                    data: 'created_by',
                    name: 'created_by'
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
@endsection
