@extends('admin.main')

@section('content')
    {!! Form::model($user, ['route' => ['admin.users.update', $user->id], 'method' => 'PUT', 'class' => 'edit-user']) !!}
        <p>
            {!! Form::label('first_name') !!}
            {!! Form::text('first_name', null, ['class' => 'edit-data']) !!}
        </p>
        <p>
            {!! Form::label('last_name') !!}
            {!! Form::text('last_name', null, ['class' => 'edit-data']) !!}
        </p>
        <p>
            {!! Form::label('email') !!}
            {!! Form::text('email', null, ['class' => 'edit-data']) !!}
        </p>
        <p><label>Roles: </label>
            {!! Form::select(
            'roles[]',
             \App\Role::all()->pluck('display_name', 'id')->toArray(),
             $user->roles->pluck('id')->toArray(),
             ['multiple', 'class' => 'new_multiple']
             ) !!}
        </p>
        @if (!$user->hasRole('admin'))
        <p>
            {!! Form::label('ban') !!}
            {!! Form::checkbox('ban', 1, (bool)$user->banned_at, ['class' => 'ban-check']) !!}
        </p>
        <p>
            {!! Form::label('ban_reason') !!}
            {!! Form::text('ban_reason', (bool)$user->ban_reason, ['class' => 'edit-data']) !!}
        </p>
        @endif
        {!! Form::submit('Save', ['class' => 'btn btn-success button-my']) !!}
    {!! Form::close() !!}
    @if(session('status'))
        Updated
    @endif
@endsection