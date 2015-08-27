@extends('admin.main')

@section('content')
    <legend>
        <a href="{{ route('admin.activitylevel.create') }}" class="btn btn-success save-spot button-my" >New</a>
        <h2>Activity level</h2>
    </legend>
    <div class="col-xs-12 padding-0">
        <table class="col-xs-12 padding-0">
            <thead>
            <tr>
                <th class="col-xs-7">Title</th>
                <th class="col-xs-4">Quantity</th>
                <th class="col-xs-1"></th>

            </tr>
            </thead>
            <tbody>
            @foreach($levels as $level)
                <tr>
                    <td><a href="{{ route('admin.activitylevel.edit', [$level->id]) }}">{{ $level->name }}</a></td>
                    <td><p>{{ $level->favorites_count }}</p></td>
                    <td>
                        {!! Form::open(['route' => ['admin.activitylevel.destroy', $level->id], 'method' => 'delete']) !!}
                        {!! Form::submit() !!}
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-xs-12 pagination"></div>
@endsection
