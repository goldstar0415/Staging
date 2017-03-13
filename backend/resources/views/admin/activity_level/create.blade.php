@extends('admin.main')

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => 'admin.activitylevel.store', 'class' => 'creat-activity-level']) !!}
        @include('admin.activity_level.form')
    {!! Form::close() !!}
@endsection