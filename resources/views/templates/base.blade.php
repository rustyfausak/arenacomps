@extends('templates.boilerplate')

@section('body')
    @include('templates.header')
    <div class="container-fluid">
        @yield('content', '')
    </div>
    @include('templates.footer')
@endsection
