@extends('admin.main')

@section('content')
<h2>
    Send email letter
</h2>
<hr>
{!! Form::open(['method' => 'POST', 'route' => 'admin.email.send', 'class' => 'mail-form']) !!}
    <div class="form-group">
        {!! Form::label('users', 'Receivers') !!}
        {!! Form::select('users[]', $users, array_keys($users), [
            'multiple',
            'id' => 'users',
            'class' => 'form-control'
        ]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('subject', 'Subject') !!}
        {!! Form::text('subject', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('body', 'Body') !!}
        {!! Form::textarea('body', null, ['class' => 'form-control ckeditor']) !!}
    </div>
    {!! Form::submit('Send', ['class' => 'btn btn-default form-control']) !!}
{!! Form::close() !!}
@endsection