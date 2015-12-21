@extends('admin.main')

@section('content')
    <h2>
        Parser settings
    </h2>
    <hr>
    {!! Form::open(['method' => 'PUT']) !!}
    <div class="form-group">
        {!! Form::label('aid', 'Aid') !!}
        {!! Form::text(
                'aid',
                isset($settings->parser->aid) ? $settings->parser->aid : null,
                ['placeholder' => 'identifier', 'class' => 'form-control']
            ) !!}
        {!! Form::submit('Save', ['class' => 'btn btn-default']) !!}
    </div>
    <p class="text-info">
        Last imported id: {{ isset($settings->parser->last_imported_id) ? $settings->parser->last_imported_id : null }}
    </p>
    {!! Form::close() !!}
@endsection