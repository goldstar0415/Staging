<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <title>ZoomTivity</title>
    <link rel="stylesheet" href="/css/admin.css" />
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
</head>


<body>

<script src="js/bootstrap.min.js"></script>

<div class="container wrap admin">
    <div class="col-md-9 col-xs-8">
        <div class="editing blog col-xs-12">
            @yield('content')
        </div>
    </div>
    @include('admin.sidebar')
</div>

<!--</div>-->



<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.js"></script>
</body>


</html>