@extends('layouts.home')
@section('title', config('app.name','KizenGroup'))
@section('content')

<style type="text/css">
    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
        margin-top: 10%;
    }

    .title {
        font-size: 84px;
    }

    .tagline {
        font-size: 25px;
        font-weight: 300;
        text-align: center;
    }

    @media only screen and (max-width: 600px) {
        .title {
            font-size: 15px;
        }

        .tagline {
            font-size: 18px;
        }
    }
    .loginbtn:hover {
        font-size: 34px;
        transition: .5s;
        background-color: #243949;
        color: white;
    }

    body{
        background-color: white;
        background-image: none;
    }
    
</style>

<div class="container text-center " style="margin-top: 10%;">
    <img src="img/logo.gif" style="width: 35%;" />
    <h1>KaizenGroup</h1>
    <a class="btn btn-light loginbtn"  style="font-size:24px;" type="button" href="{{ route('login') }}">
        <i class="fa fa-play-circle"></i>
        @lang('lang_v1.login')</a>
</div>
@endsection