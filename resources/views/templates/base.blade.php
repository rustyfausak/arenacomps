@extends('templates.boilerplate')

@section('body')
    @include('templates.header')
    <div class="page-header-bar">
        <div class="container">
            <h1>@yield('page-title', '')</h1>
            @yield('page-header-bar', '')
        </div>
    </div>
    <div class="container">
        @yield('content', '')
    </div>
    @include('templates.footer')
@endsection
