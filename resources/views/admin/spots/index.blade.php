@extends('admin.main')

@section('content')
<div class="editing col-xs-12">
    <h2>Spots</h2>
    <hr>
    <div class="row actions">
        <ul class="nav nav-pills">
            <li role="presentation">
                {!! link_to_route('admin.spots.email-savers', 'Email Savers', Request::query()) !!}
            </li>
            <li role="presentation">
                {!! link_to_route('admin.spots.email-list', 'Email List', Request::query()) !!}
            </li>
            <li role="presentation">
                {!! link_to_route('admin.spots.export-filter', 'Export Filter', Request::query()) !!}
            </li>
        </ul>
    </div>
    <div class="row actions">
        {!! Form::open(['method' => 'GET', 'route' => 'admin.spots.search', 'class' => 'search-form']) !!}
        {!! Form::text('search_text', null, ['placeholder' => 'Search by name']) !!}
        {!! Form::submit('Search') !!}
        {!! Form::close() !!}
        {!! Form::open(['method' => 'GET', 'route' => 'admin.spots.filter', 'class' => 'form-inline']) !!}
        <div class="form-group">
            {!! Form::label('filter[title]', 'Title:') !!}
            {!! Form::text('filter[title]', old('filter.title'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[description]', 'Description:') !!}
            {!! Form::text('filter[description]', old('filter.description'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[address]', 'Address:') !!}
            {!! Form::text('filter[address]', old('filter.address'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[username]', 'Username:') !!}
            {!! Form::text('filter[username]', old('filter.username'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[user_email]', 'User email:') !!}
            {!! Form::text('filter[user_email]', old('filter.user_email'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[date]', 'Event date:') !!}
            {!! Form::input('filter[date]', 'date', old('filter.date'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[created_at]', 'Created at:') !!}
            {!! Form::text('filter[created_at]', old('filter.created_at'), ['class' => 'form-control']) !!}
        </div>
        {!! Form::button('Filter', ['class' => 'btn btn-default', 'type' => 'submit']) !!}
        {!! Form::close() !!}
    </div>
    <table class="col-xs-12">
        <thead>
        <tr>
            <th id="bulk"><input type="checkbox"></th>
            <th class="col-sm-2">User</th>
            <th class="col-sm-2">Email</th>
            <th class="col-sm-3">Title</th>
            <th class="col-sm-3">Description</th>
            <th class="col-sm-2">Category</th>
            <th class="col-sm-1">Date added</th>
            <th class="col-sm-1">Event date</th>
            <th class="col-sm-1">Saves</th>
            <th class="col-sm-1">Favorites</th>
            <th class="col-sm-1">Web sites</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($spots as $spot)
            <tr>
                <td>{!! Form::checkbox('spots[]', $spot->id, null, ['class' => 'row-select']) !!}</td>
                <td>{!! link_to_route('admin.users.show', $spot->user->full_name, [$spot->user->id]) !!}</td>
                <td>{!! link_to_route('admin.users.show', $spot->user->email, [$spot->user->id]) !!}</td>
                <td>{!! link_to(frontend_url('user', $spot->user_id, 'spot', $spot->id), $spot->title) !!}</td>
                <td>{{ $spot->description }}</td>
                <td>{{ $spot->category->display_name }}</td>
                <td>{{ $spot->created_at }}</td>
                <td>{{ $spot->start_date . ' - ' . $spot->end_date }}</td>
                <td>{{ $spot->calendarUsers()->count() }}</td>
                <td>{{ $spot->favorites()->count() }}</td>
                <td>{{ $spot->web_sites ? implode(', ', $spot->web_sites) : null }}</td>
                <td>
                    {!! link_delete(route('admin.spots.destroy', [$spot->id]), '', ['class' => 'delete']) !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="row actions">
    {!! Form::open(['method' => 'PATCH', 'route' => 'admin.spots.bulk-update', 'class' => 'form-inline', 'id' => 'bulk-edit']) !!}
        <div class="form-group col-lg-2">
            {!! Form::label('users', 'User:') !!}
            {!! Form::select('users', [], null, ['id' => 'users', 'style' => 'width: 80%', 'placeholder' => 'Username']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('start_date', 'Start date:') !!}
            {!! Form::input('date', 'start_date', null, ['placeholder' => 'Y-m-d']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('end_date', 'End date:') !!}
            {!! Form::input('date', 'end_date', null, ['placeholder' => 'Y-m-d']) !!}
        </div>
        {!! Form::submit('Save', ['class' => 'btn btn-default']) !!}
    {!! Form::close() !!}
    </div>
    <div class="row actions col-lg-12">
        <div class="form-group pull-right">
            {!! Form::label('limit', 'Items per page') !!}
            {!! Form::select('limit', [15 => '15', 50 => '50', 100 => '100'], Request::get('limit')) !!}
        </div>
    </div>
    @include('admin.pagination', ['paginatable' => $spots])
</div>
@endsection