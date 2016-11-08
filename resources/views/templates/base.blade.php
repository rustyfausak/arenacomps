@extends('templates.boilerplate')

@section('body')
    @include('templates.header')
    <div class="page-header-bar">
        <div class="container">
        <div class="pull-right share"><a href="{{ $share_url }}">share</a></div>
            <h1>@yield('page-title', '')</h1>
            @yield('page-header-bar', '')
        </div>
    </div>
    <div class="container">
        @yield('content', '')
    </div>
    @include('templates.footer')
@endsection
