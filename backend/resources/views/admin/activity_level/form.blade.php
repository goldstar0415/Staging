<p>
    {!! Form::label('name', 'Title') !!}
    {!! Form::text('name', null, ['class' => 'title-level']) !!}
</p>
<p>
    {!! Form::label('favorites_count', 'Number of favourite events') !!}
    {!! Form::input('number', 'favorites_count', null, ['class' => 'number-events']) !!}
</p>

{!! Form::submit('Save', ['class' => 'btn btn-success button-my']) !!}