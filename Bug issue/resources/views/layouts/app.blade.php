@inject('request', 'Illuminate\Http\Request')

@if($request->segment(1) == 'pos' && ($request->segment(2) == 'create' || $request->segment(3) == 'edit'))
@php
$pos_layout = true;
@endphp
@else
@php
$pos_layout = false;
@endphp
@endif

@php
$whitelist = ['127.0.0.1', '::1'];
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr'}}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ Session::get('business.name') }}</title>
    @include('layouts.partials.css')
    @yield('css')


    <style>

        .select2{
            border-radius: 20px !important;
        }
        input,select{
            border-radius: 20px !important;
        }
        input:focus{
            background-color: #449DD1 !important;
            color:white !important;
            text-align: center;
            transition: 0.9s;
        }
        .box-header,
        .modal-header {
            /* background-image: url("{{ ('img/logo.gif') }}"); */
            /* background-image: url("{{ asset('img/kg_clean.png') }}"); */
            /* background-size: contain; */
            /* background-repeat: no-repeat; */
            <?php
            if (config('app.locale') == 'ar') {
                echo  "background-position: left;";
            } else {
                echo  "background-position: right;";
            }
            ?>border-radius: 10px;
        }

        .modal-content {
            border-radius: 10px !important;
        }

        .main-sidebar {
            background-color: red !important;
            border: 1px dotted #232C33 !important;
        }

        .main-header {
            border-bottom: 1px dotted #232C33 !important;
        }

        .box-primary,
        .box-warning,
        .box-solid {
            border-top: 0px !important;
            border-bottom: 3px solid #F9DC5C !important;
        }

        .TopNavCustom,
        .navbar {
            background-color: #449DD1 !important;
        }

        th {
            background-color: #F9DC5C !important;
            border-radius: 10px;
            text-align: center;
        }

        .dropdown-menu {
            border: 4px solid #F9DC5C;
            border-radius: 10px !important;
        }

        th:hover {
            background-color: #449DD1 !important;
            color: #EDEDF4;
            border-radius: 10px;
            text-align: center;
            transition: .4s;
        }

        .btn-info {
            background-color: #449DD1;
        }
    </style>

</head>

<body class="@if($pos_layout) hold-transition lockscreen @else hold-transition skin-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'blue-light'}}@endif sidebar-mini @endif">
    <div class="wrapper thetop">
        <script type="text/javascript">
            if (localStorage.getItem("upos_sidebar_collapse") == 'true') {
                var body = document.getElementsByTagName("body")[0];
                body.className += " sidebar-collapse";
            }
        </script>
        @if(!$pos_layout)
        @include('layouts.partials.header')
        @include('layouts.partials.sidebar')
        @else
        @include('layouts.partials.header-pos')
        @endif

        @if(in_array($_SERVER['REMOTE_ADDR'], $whitelist))
        <input type="hidden" id="__is_localhost" value="true">
        @endif

        <!-- Content Wrapper. Contains page content -->
        <div class="@if(!$pos_layout) content-wrapper @endif"   style="background-image: url('{{ asset('img/logo.gifx') }}');background-repeat:no-repeat;background-position:center;background-size:fill;">
            <!-- empty div for vuejs -->
            <div id="app">
                @yield('vue')
            </div>
            <!-- Add currency related field-->
            <input type="hidden" id="__code" value="{{session('currency')['code']}}">
            <input type="hidden" id="__symbol" value="{{session('currency')['symbol']}}">
            <input type="hidden" id="__thousand" value="{{session('currency')['thousand_separator']}}">
            <input type="hidden" id="__decimal" value="{{session('currency')['decimal_separator']}}">
            <input type="hidden" id="__symbol_placement" value="{{session('business.currency_symbol_placement')}}">
            <input type="hidden" id="__precision" value="{{config('constants.currency_precision', 2)}}">
            <input type="hidden" id="__quantity_precision" value="{{config('constants.quantity_precision', 2)}}">
            <!-- End of currency related field-->
            @can('view_export_buttons')
            <input type="hidden" id="view_export_buttons">
            @endcan
            @if(isMobile())
            <input type="hidden" id="__is_mobile">
            @endif
            @if (session('status'))
            <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
            @endif
            @yield('content')

            <div class='scrolltop no-print'>
                <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
            </div>

            @if(config('constants.iraqi_selling_price_adjustment'))
            <input type="hidden" id="iraqi_selling_price_adjustment">
            @endif

            <!-- This will be printed -->
            <section class="invoice print_section" id="receipt_section">
            </section>

        </div>
        @include('home.todays_profit_modal')
        <!-- /.content-wrapper -->

        @if(!$pos_layout)
        @include('layouts.partials.footer')
        @else
        @include('layouts.partials.footer_pos')
        @endif

        <audio id="success-audio">
            <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="error-audio">
            <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="warning-audio">
            <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
    </div>

    @if(!empty($__additional_html))
    {!! $__additional_html !!}
    @endif

    @include('layouts.partials.javascripts')

    <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

    @if(!empty($__additional_views) && is_array($__additional_views))
    @foreach($__additional_views as $additional_view)
    @includeIf($additional_view)
    @endforeach
    @endif
</body>

</html>