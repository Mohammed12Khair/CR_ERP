@extends('layouts.app')
@section('title', __('restaurant.bookings'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'restaurant.bookings' )</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    <br>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('restaurant.todays_bookings')</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-bordered table-condensssed" id="todays_bookings_table_2">
                        <thead>
                            <tr>
                                <th>@lang('contact.customer')</th>
                                <th>@lang('restaurant.booking_starts')</th>
                                <th>@lang('restaurant.booking_ends')</th>
                                <th>تعليق</th>
                                <th>حاله الحجز</th>
                                <th>الفاتوره</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($query as $query_line)
                            <tr>
                                <td>
                                    <?php
                                    // echo $query_line->contact_id;
                                    try {
                                        $name = App\Contact::where('id', $query_line->contact_id)->first()->first_name;
                                        if ($name == null) {
                                            echo "Walk-in Customer";
                                        } else {
                                            echo $name;
                                        }
                                    } catch (Exception $e) {
                                        echo "Walk-in Customer";
                                    }
                                    ?>
                                </td>
                                <td>{{$query_line->booking_start}}</td>
                                <td>{{$query_line->booking_end}}</td>
                                <td>{{$query_line->booking_note}}</td>
                                <td>
                                    <?php
                                    echo __('lang_v1.' . $query_line->booking_status);
                                    ?>
                                </td>
                                <td>

                                    @can('booking.LinkInvoice')
                                    <?php
                                    if ($query_line->booking_invoice == 0) {
                                        echo "<a href='" . action('Restaurant\BookingController@linkInvoiceGrand', [$id, $query_line->id, 'grand']) . "' class='btn btn-sm btn-success'>تاكيد الحجز</a>";
                                    }
                                    ?>
                                    @endcan

                                    @can('booking.unLinkInvoice')
                                    <?php
                                    if ($query_line->booking_invoice > 0) {
                                        echo "<a href='" . action('Restaurant\BookingController@linkInvoiceGrand', [$id, $query_line->id, 'delete']) . "' class='btn btn-sm btn-danger'> سحب الفاتورة</a>";
                                    }
                                    ?>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    </div>

</section>
<!-- /.content -->


@endsection

@section('javascript')

<script type="text/javascript">
    $(document).ready(function() {
        clickCount = 0;
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,listWeek'
            },
            eventLimit: 2,
            events: '/bookings',
            eventRender: function(event, element) {
                var title_html = event.customer_name;
                if (event.table) {
                    title_html += '<br>' + event.table;
                }
                // title_html += '<br>' + event.start_time + ' - ' + event.end_time;

                element.find('.fc-title').html(title_html);
                element.attr('data-href', event.url);
                element.attr('data-container', '.view_modal');
                element.addClass('btn-modal');
            },
            dayClick: function(date, jsEvent, view) {
                clickCount++;
                if (clickCount == 2) {
                    $('#add_booking_modal').modal('show');
                    $('form#add_booking_form #start_time').data("DateTimePicker").date(date).ignoreReadonly(true);
                    $('form#add_booking_form #end_time').data("DateTimePicker").date(date).ignoreReadonly(true);
                }
                var clickTimer = setInterval(function() {
                    clickCount = 0;
                    clearInterval(clickTimer);
                }, 500);
            }
        });

        //If location is set then show tables.

        $('#add_booking_modal').on('shown.bs.modal', function(e) {
            getLocationTables($('select#booking_location_id').val());
            $(this).find('select').each(function() {
                if (!($(this).hasClass('select2'))) {
                    $(this).select2({
                        dropdownParent: $('#add_booking_modal')
                    });
                }
            });
            booking_form_validator = $('form#add_booking_form').validate({
                submitHandler: function(form) {
                    var data = $(form).serialize();

                    $.ajax({
                        method: "POST",
                        url: $(form).attr("action"),
                        dataType: "json",
                        data: data,
                        beforeSend: function(xhr) {
                            __disable_submit_button($(form).find('button[type="submit"]'));
                        },
                        success: function(result) {
                            if (result.success == true) {
                                if (result.send_notification) {
                                    $("div.view_modal").load(result.notification_url, function() {
                                        $(this).modal('show');
                                    });
                                }

                                $('div#add_booking_modal').modal('hide');
                                toastr.success(result.msg);
                                reload_calendar();
                                todays_bookings_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                            $(form).find('button[type="submit"]').attr('disabled', false);
                        }
                    });
                }
            });
        });
        $('#add_booking_modal').on('hidden.bs.modal', function(e) {
            booking_form_validator.destroy();
            reset_booking_form();
        });

        $('form#add_booking_form #start_time').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            minDate: moment(),
            ignoreReadonly: true
        });

        $('form#add_booking_form #end_time').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            minDate: moment(),
            ignoreReadonly: true,
        });

        $('.view_modal').on('shown.bs.modal', function(e) {
            $('form#edit_booking_form').validate({
                submitHandler: function(form) {
                    var data = $(form).serialize();

                    $.ajax({
                        method: "PUT",
                        url: $(form).attr("action"),
                        dataType: "json",
                        data: data,
                        beforeSend: function(xhr) {
                            __disable_submit_button($(form).find('button[type="submit"]'));
                        },
                        success: function(result) {
                            if (result.success == true) {
                                $('div.view_modal').modal('hide');
                                toastr.success(result.msg);
                                reload_calendar();
                                todays_bookings_table.ajax.reload();
                                $(form).find('button[type="submit"]').attr('disabled', false);
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });

        todays_bookings_table_2 = $('#todays_bookings_table_2').DataTable({
            processing: true,
            serverSide: false,
            "ordering": true,
            'searching': true,
        });
    });
    $(document).on('change', 'select#booking_location_id', function() {
        getLocationTables($(this).val());
    });

    $(document).on('change', 'select#business_location_id', function() {
        reload_calendar();
        todays_bookings_table.ajax.reload();
    });

    $(document).on('click', 'button#delete_booking', function() {
        swal({
            title: LANG.sure,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).data('href');
                $.ajax({
                    method: "DELETE",
                    url: href,
                    dataType: "json",
                    success: function(result) {
                        if (result.success == true) {
                            $('div.view_modal').modal('hide');
                            toastr.success(result.msg);
                            reload_calendar();
                            todays_bookings_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });

    function getLocationTables(location_id) {
        $.ajax({
            method: "GET",
            url: '/modules/data/get-pos-details',
            data: {
                'location_id': location_id
            },
            dataType: "html",
            success: function(result) {
                $('div#restaurant_module_span').html(result);
            }
        });
    }

    function reset_booking_form() {
        $('select#booking_location_id').val('').change();
        // $('select#booking_customer_id').val('').change();
        $('select#correspondent').val('').change();
        $('#booking_note, #start_time, #end_time').val('');
    }

    function reload_calendar() {
        var location_id = '';
        if ($('select#business_location_id').val()) {
            location_id = $('select#business_location_id').val();
        }

        var events_source = {
            url: '/bookings',
            type: 'get',
            data: {
                'location_id': location_id
            }
        }
        $('#calendar').fullCalendar('removeEventSource', events_source);
        $('#calendar').fullCalendar('addEventSource', events_source);
        $('#calendar').fullCalendar('refetchEvents');
    }

    $(document).on('click', '.add_new_customer', function() {
        $('.contact_modal')
            .find('select#contact_type')
            .val('customer')
            .closest('div.contact_type_div')
            .addClass('hide');
        $('.contact_modal').modal('show');
    });
    $('form#quick_add_contact')
        .submit(function(e) {
            e.preventDefault();
        })
        .validate({
            rules: {
                contact_id: {
                    remote: {
                        url: '/contacts/check-contacts-id',
                        type: 'post',
                        data: {
                            contact_id: function() {
                                return $('#contact_id').val();
                            },
                            hidden_id: function() {
                                if ($('#hidden_id').length) {
                                    return $('#hidden_id').val();
                                } else {
                                    return '';
                                }
                            },
                        },
                    },
                },
            },
            messages: {
                contact_id: {
                    remote: LANG.contact_id_already_exists,
                },
            },
            submitHandler: function(form) {
                var data = $(form).serialize();
                $.ajax({
                    method: 'POST',
                    url: $(form).attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button($(form).find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('select#booking_customer_id').append(
                                $('<option>', {
                                    value: result.data.id,
                                    text: result.data.name
                                })
                            );
                            $('select#booking_customer_id')
                                .val(result.data.id)
                                .trigger('change');
                            $('div.contact_modal').modal('hide');
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            },
        });
    $('.contact_modal').on('hidden.bs.modal', function() {
        $('form#quick_add_contact')
            .find('button[type="submit"]')
            .removeAttr('disabled');
        $('form#quick_add_contact')[0].reset();
    });
</script>
@endsection