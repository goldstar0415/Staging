@extends('admin.main')

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => 'admin.spot-import-columns', 'class' => 'columns-import form-inline', 'files' => true]) !!}
    @if (Request::has('code'))
        {!! Form::hidden('code', Request::get('code')) !!}
    @endif
    <div>
        {!! Form::label('spot_type', 'Choose Spot Type:') !!}
        {!! Form::select('spot_type', App\SpotType::all()->pluck('display_name', 'name'), 'event', ['class' => 'form-control']) !!}
    </div>
    <div>
        {!! Form::label('spot_category', 'Choose Spot Category: ') !!}
        {!! Form::select(
                'spot_category',
                App\SpotType::where('name', 'event')->first()->categories->pluck('display_name', 'id'),
                null,
                ['size' => 3, 'class' => 'form-group']
            )
        !!}
    </div>
    <div class="columns">
        <div class="form-group">
            {!! Form::label('title') !!}
            {!! Form::textarea('title', null, ['class' => 'form-control', 'cols' => 10, 'rows' => 20]) !!}
        </div>
        <div class="form-group">
            {!! Form::label('description') !!}
            {!! Form::textarea('description', null, ['class' => 'form-control', 'cols' => 10, 'rows' => 20]) !!}
        </div>
        <div class="form-group">
            {!! Form::label('website') !!}
            {!! Form::textarea('websites', null, ['class' => 'form-control', 'cols' => 10, 'rows' => 20]) !!}
        </div>
        <div class="form-group">
            {!! Form::label('latitude') !!}
            {!! Form::textarea('latitude', null, ['class' => 'form-control', 'cols' => 10, 'rows' => 20]) !!}
        </div>
        <div class="form-group">
            {!! Form::label('longitude') !!}
            {!! Form::textarea('longitude', null, ['class' => 'form-control', 'cols' => 10, 'rows' => 20]) !!}
        </div>
        <div class="form-group">
            {!! Form::label('full_address') !!}
            {!! Form::textarea('address', null, ['class' => 'form-control', 'cols' => 10, 'rows' => 20]) !!}
        </div>
        <div class="form-group">
            {!! Form::label('Picture') !!}
            {!! Form::textarea('picture', null, ['class' => 'form-control', 'cols' => 10, 'rows' => 20]) !!}
        </div>
        <div class="form-group">
            {!! Form::label('email') !!}
            {!! Form::textarea('e_mail', null, ['class' => 'form-control', 'cols' => 10, 'rows' => 20]) !!}
        </div>
        <div class="form-group event-only">
            {!! Form::label('start_date') !!}
            {!! Form::textarea('start_date', null, ['class' => 'form-control', 'cols' => 10, 'rows' => 20]) !!}
        </div>
        <div class="form-group event-only">
            {!! Form::label('end_date') !!}
            {!! Form::textarea('end_date', null, ['class' => 'form-control', 'cols' => 10, 'rows' => 20]) !!}
        </div>
    </div>
    <div class="ins-photos">
        <div class="form-group">
            {!! Form::label('ins_photos', 'Take instagram photos') !!}
            {!! Form::checkbox('ins_photos', 1, null, ['class' => 'form-control']) !!}
        </div>
        <div>
            {!! Form::label('ins_token', 'Set instagram token') !!}
            {!! Form::text('ins_token', null, ['class' => 'form-control']) !!}
            <span class="help-block">You can get access token from <a href="https://elfsight.com/service/get-instagram-access-token/">Elfsight</a></span>
        </div>
        <div>
            {!! Form::label('get_address', 'Get address') !!}
            {!! Form::checkbox('get_address', 1, null, ['class' => 'form-control']) !!}
            <span class="help-block">Get address from google api</span>
        </div>
    </div>
    <p>
        {!! Form::submit('Save', ['class' => 'btn btn-success button-my']) !!}
        {!! Form::submit('Preview', ['name' => 'preview', 'class' => 'btn btn-info button-my']) !!}
    </p>
    {!! Form::close() !!}
    <p>
        {!! link_to_route('admin.spot-import.log.show', 'Show log', ['type' => 'event'], [
            'class' => 'btn btn-primary button-my',
            'id' => 'log_link',
            'target' => '_blank'
        ]) !!}
        {!! link_delete(
                route('admin.spot-import.log.delete'),
                'Delete log',
                ['class' => 'btn btn-danger button-my delete', 'id' => 'deleteLog'],
                ['type' => 'event']
            )
        !!}
    </p>
    @if (isset($spots))
    <table class="col-xs-12">
        <thead>
        <tr>
            <th>E-mail</th>
            <th>Title</th>
            <th>Description</th>
            <th>Addresses</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Rating</th>
            <th>Websites</th>
            @if (isset($spots[0]->start_date))
            <th>Start date</th>
            <th>End date</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @foreach($spots as $spot)
            <tr>
                <td>{{ $spot->email }}</td>
                <td>{{ $spot->title }}</td>
                <td>{{ $spot->description }}</td>
                <td>{{ $spot->address }}</td>
                <td>{{ $spot->latitude }}</td>
                <td>{{ $spot->longitude }}</td>
                <td>{{ $spot->rating }}</td>
                <td>{{ $spot->websites }}</td>
                @if (isset($spot->start_date))
                <td>{{ $spot->start_date }}</td>
                <td>{{ $spot->end_date }}</td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
    @if (Session::has('import'))
        <p>
            @if(Session::get('import'))
                Import successful
            @else
                Import failed
            @endif
        </p>
    @endif
    @if (Session::has('log_delete'))
        <p>
            @if(Session::get('log_delete'))
                Log deleted
            @else
                Failed log delete
            @endif
        </p>
    @endif
@endsection