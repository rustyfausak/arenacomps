<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'arenacomps')</title>

    <link rel="shortcut icon" href="{{ url('favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('css/app.css') }}">

    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    @yield('head' ,'')
</head>
<body>
    @yield('body', '')

    <script src="{{ url('js/app.js') }}"></script>

    @yield('foot', '')
</body>
</html>
