@extends('admin.main')

@section('content')
    {!! Form::model($level, [
        'method' => 'PUT',
        'route' => ['admin.activitylevel.update', $level->id],
        'class' => 'creat-activity-level'
    ]) !!}
    @include('admin.activity_level.form')
    {!! Form::close() !!}
@endsection