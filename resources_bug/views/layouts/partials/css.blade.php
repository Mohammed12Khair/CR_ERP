<link rel="stylesheet" href="{{ asset('css/vendor.css?v='.$asset_v) }}">

@if( in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) )
<link rel="stylesheet" href="{{ asset('css/rtl.css?v='.$asset_v) }}">
@endif

@yield('css')

<!-- app css -->
<link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">

@if(isset($pos_layout) && $pos_layout)
<style type="text/css">
	.content {
		padding-bottom: 0px !important;
	}
</style>
@endif
<style type="text/css">
	/*
	* Pattern lock css
	* Pattern direction
	* http://ignitersworld.com/lab/patternLock.html
	*/
	.patt-wrap {
		z-index: 10;
	}

	.patt-circ.hovered {
		background-color: #cde2f2;
		border: none;
	}

	.patt-circ.hovered .patt-dots {
		display: none;
	}

	.patt-circ.dir {
		background-image: url("{{asset('/img/pattern-directionicon-arrow.png')}}");
		background-position: center;
		background-repeat: no-repeat;
	}

	.patt-circ.e {
		-webkit-transform: rotate(0);
		transform: rotate(0);
	}

	.patt-circ.s-e {
		-webkit-transform: rotate(45deg);
		transform: rotate(45deg);
	}

	.patt-circ.s {
		-webkit-transform: rotate(90deg);
		transform: rotate(90deg);
	}

	.patt-circ.s-w {
		-webkit-transform: rotate(135deg);
		transform: rotate(135deg);
	}

	.patt-circ.w {
		-webkit-transform: rotate(180deg);
		transform: rotate(180deg);
	}

	.patt-circ.n-w {
		-webkit-transform: rotate(225deg);
		transform: rotate(225deg);
	}

	.patt-circ.n {
		-webkit-transform: rotate(270deg);
		transform: rotate(270deg);
	}

	.patt-circ.n-e {
		-webkit-transform: rotate(315deg);
		transform: rotate(315deg);
	}
</style>
@if(!empty($__system_settings['additional_css']))
{!! $__system_settings['additional_css'] !!}
@endif


<style>
	#username,
	#password {
		border-radius: 30px;
	}

	.eq-height-col {
		background-color: white;
		color: black !important;
	}

	label {

		color: black !important;
	}



	.btn-success,
	.btn-primary,
	.btn-info,
	.btn-danger,
	.btn-warning,
	.btn-default {
		border-radius: 20px !important;
	}

	.btn-default:hover {
		background-color: white !important;
		border: 1px solid black;
		color: black;
		font-weight: bold;
		transition: .5s;
	}

	.btn-success:hover {
		/* border-radius: 2px !important; */
		background-color: white !important;
		color: rgba(45, 206, 137, 100) !important;
		transition: .5s;
		box-shadow: rgba(45, 206, 137, 0.25) 0px 54px 65px,
			rgba(45, 206, 137, 0.12) 0px -12px 40px,
			rgba(45, 206, 137, 0.12) 0px 4px 6px,
			rgba(45, 206, 137, 0.17) 0px 12px 23px,
			rgba(45, 206, 137, 0.09) 0px -3px 20px !important;
	}

	.btn-danger:hover {
		/* border-radius: 2px !important; */
		background-color: white !important;
		color: rgba(45, 206, 137, 100) !important;
		transition: .5s;
		box-shadow: rgba(45, 206, 137, 0.25) 0px 54px 65px,
			rgba(45, 206, 137, 0.12) 0px -12px 40px,
			rgba(45, 206, 137, 0.12) 0px 4px 6px,
			rgba(45, 206, 137, 0.17) 0px 12px 23px,
			rgba(45, 206, 137, 0.09) 0px -3px 20px !important;
	}


	.btn-warning:hover {
		/* border-radius: 2px !important; */
		background-color: white !important;
		color: rgba(45, 206, 137, 100) !important;
		transition: .5s;
		box-shadow: rgba(45, 206, 137, 0.25) 0px 54px 65px,
			rgba(45, 206, 137, 0.12) 0px -12px 40px,
			rgba(45, 206, 137, 0.12) 0px 4px 6px,
			rgba(45, 206, 137, 0.17) 0px 12px 23px,
			rgba(45, 206, 137, 0.09) 0px -3px 20px !important;
	}

	.btn-primary:hover {
		/* border-radius: 2px !important; */
		background-color: white !important;
		color: rgba(21, 114, 232, 100) !important;
		transition: .5s !important;
		box-shadow: rgba(21, 114, 232, 0.25) 0px 54px 65px,
			rgba(21, 114, 232, 0.12) 0px -12px 40px,
			rgba(21, 114, 232, 0.12) 0px 4px 6px,
			rgba(21, 114, 232, 0.17) 0px 12px 23px,
			rgba(21, 114, 232, 0.09) 0px -3px 20px !important;
	}


	.treeview a i {
		color: lightskyblue !important;
	}

	.sidebar-menu li a i {
		color: lightskyblue !important;
	}

	.sidebar-menu {
		/* background-color: #f8f9fe !important; */
		background-color: #f1f2f8 !important;
	}

	.treeview>a {
		background-color: white !important;
		/* border: 2px solid red; */
		border-radius: 50px;
	}

	.treeview {
		padding-top: 2% !important;
		/* background-color: red !important; */
	}

	.treeview-menu>li:hover {
		background-color: white !important;
		/* border: 2px solid red; */
		border-radius: 50px;
	}

	.treeview-menu li a:hover {
		background-color: lightskyblue;
		border: 1px solid lightskyblue;
		border-top-left-radius: 20px;
		border-bottom-left-radius: 20px;
		transition-timing-function: linear;
		/* transition-timing-function: ease-in-out; */
		transition: 1s;

	}
</style>