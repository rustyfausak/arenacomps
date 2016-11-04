@extends('templates.boilerplate')

@section('body')
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="{{ route('index') }}">
                    arenacomps
                </a>
            </div>
            <ul class="nav navbar-nav">
                <li><a href="{{ route('index') }}">Leaderboard</a></li>
                <li><a href="{{ route('comps') }}">Comps</a></li>
            </ul>
        </div>
    </nav>
    <div class="container-fluid">
        @yield('content', '')
    </div>
@endsection
