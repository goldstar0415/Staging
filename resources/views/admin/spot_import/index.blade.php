@extends('admin.main')

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => 'admin.spot-import', 'class' => 'edit-user', 'files' => true]) !!}
    <p>
        {!! Form::label('spot_type', 'Choose Spot Type:') !!}
        {!! Form::select('spot_type', App\SpotType::all()->pluck('display_name', 'id')) !!}
    </p>
    <p>
        {!! Form::label('category', 'Choose Spot Category: ') !!}
        {!! Form::select(
                'category',
                App\SpotType::where('name', 'event')->first()->categories->pluck('display_name', 'id'),
                null,
                ['size' => 3]
            )
        !!}
    </p>
    <p>
        {!! Form::label('admin', 'Attached admin: ') !!}
        {!! Form::select(
                'admin',
                App\Role::where('name', 'admin')->first()->users->pluck('first_name', 'id'),
                null,
                ['size' => 3]
            )
        !!}
    </p>
    <p>
        {!! Form::label('document') !!}
        {!! Form::file('document') !!}
    </p>
    <p>
        {!! Form::submit('Save', ['class' => 'btn btn-success button-my']) !!}
        {!! Form::close() !!}
    </p>
    <p>
        {!! link_to_route('admin.spot-import.log.show', 'Show log', ['type' => 'event'], ['class' => 'btn btn-primary button-my']) !!}
        {!! link_delete(
                route('admin.spot-import.log.delete'),
                'Delete log',
                ['class' => 'btn btn-danger button-my'],
                ['type' => 'event']
            )
        !!}
    </p>
    @if(Session::has('import'))
        <p>
            @if(Session::get('import'))
                Import successful
            @else
                Import failed
            @endif
        </p>
    @endif
    @if(Session::has('log_delete'))
        <p>
            @if(Session::get('log_delete'))
                Log deleted
            @else
                Failed log delete
            @endif
        </p>
    @endif
@endsection