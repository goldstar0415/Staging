<!doctype html>
<html class="no-js" lang="ru-RU">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="{!! asset('apple-touch-icon.png') !!}">
    <!-- Place favicon.ico in the root directory -->

    <link rel="shortcut icon" href="{!! asset('favicon.ico') !!}" type="image/x-icon">
    <link rel="stylesheet" href="{!! asset('css/style.css') !!}">
    <script src="{!! asset('js/main.min.js') !!}"></script>
</head>
<body>
<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<!-- Start -->
<div class="wrapper" ng-app="main">
    <header role="banner">
    </header>
    <aside class="sidebar">
    </aside>
    <nav role="navigation">
        <ul id="simple">
            <li><a href="#">Tab</a></li>
        </ul>
    </nav>
    <main role="main">
        @yield('content')
    </main>
    <footer role="contentinfo">
    </footer>
</div>
<!-- End -->

</body>
</html>