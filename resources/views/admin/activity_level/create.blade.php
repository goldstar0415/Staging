@extends('admin.main')

@section('content')
{!! Form::open(['method' => 'POST', 'route' => 'admin.activitylevel.store', 'class' => 'creat-activity-level']) !!}
    <p>
        {!! Form::label('title', 'Title') !!}
        {!! Form::text('name', null, ['class' => 'title-level']) !!}
    </p>
    <p>
        {!! Form::label('favorites_count', 'Number of favourites events') !!}
        {!! Form::input('number', 'favorites_count', null, ['class' => 'number-events']) !!}
    </p>

    {!! Form::submit('Save', ['class' => 'btn btn-success button-my']) !!}
{!! Form::close() !!}
@endsection