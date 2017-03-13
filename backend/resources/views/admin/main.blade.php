<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <title>ZoomTivity</title>

    <link rel="stylesheet" href="/css/admin.css" />
</head>


<body>

<div class="container-fluid wrap admin">
    <div class="admin-main col-md-10 col-xs-8">
        @include('admin.errors')
        <div class="editing blog row">
            @yield('content')
        </div>
    </div>
    @include('admin.sidebar')
</div>

<!--</div>-->



<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/js/bootstrap.min.js"></script>

<!--SELECT2-->
<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1-rc.1/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1-rc.1/js/select2.min.js"></script>

<!--CKeditor-->
<script src="//cdn.ckeditor.com/4.5.5/full/ckeditor.js"></script>

<!--Others-->
<script src="/js/admin.js"></script>

@yield('scripts')

</body>


</html>