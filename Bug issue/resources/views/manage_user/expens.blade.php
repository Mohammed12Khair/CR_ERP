@extends('layouts.app')
@section('title', __( 'user.users' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'user.users' )
        <small>@lang( 'user.manage_users' )</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'user.all_users' )])
    @can('user.create')
    @slot('tool')
    <!-- <div class="box-tools">
                    <a class="btn btn-block btn-primary" 
                    href="{{action('ManageUserController@create')}}" >
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                 </div> -->
    @endslot
    @endcan
    @can('user.view')
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="users_tadble" style="text-align: center;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Account</th>
                    <!-- <th>Account_ID</th> -->
                    <!-- <th>@lang( 'messages.action' )</th> -->
                    <th><img src="{{ asset('img/gear.gif') }}" width="25"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($All_Accounts as $All_Account)
                <tr>
                    <td>{{$All_Account->id}}</td>
                    <td>{{$All_Account->name}}</td>
                    @if (in_array($All_Account->id, $active_accounts_data))
                    <td>
                        <form action="{{action('ManageUserController@ExpensManageDelete')}}" method="post">
                            @csrf
                            <input name="userid" value="{{$id}}" type="text"  readonly hidden>
                            <input name="account_id" value="{{$All_Account->id}}" type="text"  readonly hidden>
                            <button class="btn btn-sm btn-danger"><i class="glyphicon glyphicon-trash"></i></button>
                        </form>
                    </td>
                    @else
                    <td>
                        <form action="{{action('ManageUserController@ExpensManageAdd')}}" method="post">
                            @csrf
                            <input name="userid" value="{{$id}}" type="text" readonly hidden>
                            <input name="account_id" value="{{$All_Account->id}}" type="text" readonly hidden>
                            <button class="btn btn-sm btn-success"><i class="fa fa-plus"></i></button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endcan
    @endcomponent

    <div class="modal fade user_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    //Roles table
    $(document).ready(function() {
        var users_table = $('#users_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/users',
            columnDefs: [{
                "targets": [4],
                "orderable": false,
                "searchable": false
            }],
            "columns": [{
                    "data": "username"
                },
                {
                    "data": "full_name"
                },
                {
                    "data": "role"
                },
                {
                    "data": "email"
                },
                {
                    "data": "action"
                }
            ]
        });
        $(document).on('click', 'button.delete_user_button', function() {
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_user,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                users_table.ajax.reload();
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