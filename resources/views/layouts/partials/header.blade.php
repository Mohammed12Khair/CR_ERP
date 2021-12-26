@inject('request', 'Illuminate\Http\Request')
<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header" style="text-align: center;">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ config('about.companyname') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="text-align: center;color:black;">
        <img src="{{ asset('img/logo.gif') }}" style="width: 100%;margin-left:10px;margin-right:10px;">
        {{ config('about.about') }}
        <br>
        <a href=" {{ config('about.website') }}"> {{ config('about.website') }}</a>
      </div>
      <div class="modal-footer">
        <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> -->
        <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
      </div>
    </div>
  </div>
</div>


<!-- Main Header -->
<header class="main-header no-print">
  <button class="logo TopNavCustom " type="button" data-toggle="modal" data-target="#exampleModalCenter">
    <img src="{{ asset('img/logo.gif') }}" style="background-color:white;border-radius: 25% 10%;" class="giflogo">
  </button>

  <!-- Header Navbar -->
  <nav class="navbar navbar-static-top" role="navigation">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
      &#9776;
      <span class="sr-only">Toggle navigation</span>
    </a>

    @if(Module::has('Superadmin'))
    @includeIf('superadmin::layouts.partials.active_subscription')
    @endif

    <!-- Navbar Right Menu -->
    <div class="navbar-custom-menu">

      @if(Module::has('Essentials'))
      @includeIf('essentials::layouts.partials.header_part')
      @endif

      <div class="btn-group">
        <button id="header_shortcut_dropdown" type="button" class="btn btn-success dropdown-toggle btn-flat pull-left m-8 btn-sm mt-10" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-plus-circle fa-lg"></i>
        </button>
        <ul class="dropdown-menu">
          @if(config('app.env') != 'demo')
          <li><a href="{{route('calendar')}}">
              <i class="fas fa-calendar-alt" aria-hidden="true"></i> @lang('lang_v1.calendar')
            </a></li>
          @endif
          @if(Module::has('Essentials'))
          <li><a href="#" class="btn-modal" data-href="{{action('\Modules\Essentials\Http\Controllers\ToDoController@create')}}" data-container="#task_modal">
              <i class="fas fa-clipboard-check" aria-hidden="true"></i> @lang( 'essentials::lang.add_to_do' )
            </a></li>
          @endif
          <!-- Help Button -->
          @if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id))
          <li><a id="start_tour" href="#">
              <i class="fas fa-question-circle" aria-hidden="true"></i> @lang('lang_v1.application_tour')
            </a></li>
          @endif
        </ul>
      </div>
      <button id="btnCalculator" title="@lang('lang_v1.calculator')" type="button" class="btn btn-success btn-flat pull-left m-8 btn-sm mt-10 popover-default hidden-xs" data-toggle="popover" data-trigger="click" data-content='@include("layouts.partials.calculator")' data-html="true" data-placement="bottom">
        <strong><i class="fa fa-calculator fa-lg" aria-hidden="true"></i></strong>
      </button>

      <!-- List -->
      <!-- <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Dropdown button
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <a class="dropdown-item" href="#">Action</a>
          <a class="dropdown-item" href="#">Another action</a>
          <a class="dropdown-item" href="#">Something else here</a>
        </div>
      </div> -->
      <!-- List end -->

      @if($request->segment(1) == 'pos')
      @can('view_cash_register')
      <button type="button" id="register_details" title="{{ __('cash_register.register_details') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat pull-left m-8 btn-sm mt-10 btn-modal" data-container=".register_details_modal" data-href="{{ action('CashRegisterController@getRegisterDetails')}}">
        <strong><i class="fa fa-briefcase fa-lg" aria-hidden="true"></i></strong>
      </button>
      @endcan
      @can('close_cash_register')
      <button type="button" id="close_register" title="{{ __('cash_register.close_register') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-danger btn-flat pull-left m-8 btn-sm mt-10 btn-modal" data-container=".close_register_modal" data-href="{{ action('CashRegisterController@getCloseRegister')}}">
        <strong><i class="fa fa-window-close fa-lg"></i></strong>
      </button>
      @endcan
      @endif

      @if(in_array('pos_sale', $enabled_modules))
      @can('sell.create')
      <a href="{{action('SellPosController@create')}}" title="@lang('sale.pos_sale')" data-toggle="tooltip" data-placement="bottom" class="btn btn-flat pull-left m-8 btn-sm mt-10 btn-success">
        <img src="{{ asset('img/pos.jpg') }}" width="25" style="border-radius: 10px;">
        <!-- <strong><i class="fa fa-th-large"></i> &nbsp; @lang('sale.pos_sale')</strong> -->
      </a>
      @endcan
      @endif
      @if(Module::has('Repair'))
      @includeIf('repair::layouts.partials.header')
      @endif
      @can('profit_loss_report.view')
      <button type="button" id="view_todays_profit" title="{{ __('home.todays_profit') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat pull-left m-8 btn-sm mt-10">
        <!-- <strong><i class="fas fa-money-bill-alt fa-lg"></i></strong> -->
        <img src="{{ asset('img/cash.png') }}" width="25" style="border-radius: 10px;">
      </button>
      @endcan

      <div class="m-8 pull-left mt-15 hidden-xs" style="color: #fff;"><strong>{{ @format_date('now') }}</strong></div>

      <ul class="nav navbar-nav">
        @include('layouts.partials.header-notifications')
        <!-- User Account Menu -->
        <li class="dropdown user user-menu">
          <!-- Menu Toggle Button -->
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <!-- The user image in the navbar-->
            @php
            $profile_photo = auth()->user()->media;
            @endphp
            @if(!empty($profile_photo))
            <img src="{{$profile_photo->display_url}}" class="user-image" alt="User Image">
            @endif
            <!-- hidden-xs hides the username on small devices so only the image appears. -->
            <span>{{ Auth::User()->first_name }} {{ Auth::User()->last_name }}</span>
          </a>
          <ul class="dropdown-menu">
            <!-- The user image in the menu -->
            <li class="user-header">
              @if(!empty(Session::get('business.logo')))
              <img src="{{ asset( 'uploads/business_logos/' . Session::get('business.logo') ) }}" alt="Logo">
              @endif
              <p>
                {{ Auth::User()->first_name }} {{ Auth::User()->last_name }}
              </p>
            </li>
            <!-- Menu Body -->
            <!-- Menu Footer-->
            <li class="user-footer">
              <div class="pull-left">
                <a href="{{action('UserController@getProfile')}}" class="btn btn-default btn-flat">@lang('lang_v1.profile')</a>
              </div>
              <div class="pull-right">
                <a href="{{action('Auth\LoginController@logout')}}" class="btn btn-default btn-flat">@lang('lang_v1.sign_out')</a>
              </div>
            </li>
          </ul>
        </li>
        <!-- Control Sidebar Toggle Button -->
      </ul>
    </div>
  </nav>
</header>