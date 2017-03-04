@extends('admin.main')

@section('content')
<h2 class="col-xs-12">Parser</h2>
<hr class="col-xs-12" />
<div class="clearfix"></div>
<div class="row actions">
    {!! Form::open(['method' => 'POST', 'route' => 'admin.csv-parser.export-upload', 'class' => 'export-form', 'files' => true]) !!}
    @include('admin.parser.controls')
    {!! Form::close() !!}
    <div class="clearfix"></div>
    @include('admin.parser.timer')
    @include('admin.parser.progress')
    <div class="clearfix"></div>
    @include('admin.parser.console')   
</div>

@endsection
@section('scripts')
    @include('admin.parser.scripts', ['uploadRoute' => 'admin.csv-parser.export'])
@endsection