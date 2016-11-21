<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'arenacomps')</title>

    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('css/app.css') }}?v=@include('snippets.asset-version')">

    <!--[if lt IE 9]>
      <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->


    @stack('head')
</head>
<body>
    @yield('body', '')

    <script src="//ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>
    <script src="{{ url('js/app.js') }}?v=@include('snippets.asset-version')"></script>

    @if (App::environment('production'))
        @include('snippets.google-tracking')
    @endif

    @stack('foot')
</body>
</html>
