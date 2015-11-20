@extends('admin.main')

@section('content')
<div class="editing col-xs-12">
    <h2>Spots</h2>
    <hr>
    <div class="row actions">
        <ul class="nav nav-pills">
            <li role="presentation">{!! link_to_route('admin.spots.email-savers', 'Email Savers') !!}</li>
            <li role="presentation"><a href="#">Email list</a></li>
            <li role="presentation"><a href="#">Export filter</a></li>
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
            {!! Form::text('filter[title]', null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[description]', 'Description:') !!}
            {!! Form::text('filter[description]', null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[address]', 'Address:') !!}
            {!! Form::text('filter[address]', null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[username]', 'Username:') !!}
            {!! Form::text('filter[username]', null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[user_email]', 'User email:') !!}
            {!! Form::text('filter[user_email]', null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[date]', 'Event date:') !!}
            {!! Form::input('filter[date]', 'date', null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[created_at]', 'Created at:') !!}
            {!! Form::text('filter[created_at]', null, ['class' => 'form-control']) !!}
        </div>
        {!! Form::button('Filter', ['class' => 'btn btn-default', 'type' => 'submit']) !!}
        {!! Form::close() !!}
    </div>
    <table class="col-xs-12">
        <thead>
        <tr>
            <th id="bulk"><input type="checkbox"></th>
            <th class="col-sm-3">Title</th>
            <th class="col-sm-3">Description</th>
            <th class="col-sm-2">Category</th>
            <th class="col-sm-2">Date added</th>
            <th class="col-sm-2">Start date</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($spots as $spot)
            <tr>
                <td><input type="checkbox"></td>
                <td>{!! link_to(frontend_url('user', $spot->user_id, 'spot', $spot->id), $spot->title) !!}</td>
                <td>{{ $spot->description }}</td>
                <td>{{ $spot->category->display_name }}</td>
                <td>{{ $spot->created_at }}</td>
                <td>{{ $spot->start_date }}</td>
                <td>
                    {!! link_delete(route('admin.spots.destroy', [$spot->id]), '', ['class' => 'delete']) !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="row actions col-lg-12">
        <div class="form-group pull-right">
            {!! Form::label('limit', 'Items per page') !!}
            {!! Form::select('limit', [15 => '15', 50 => '50', 100 => '100'], Request::get('limit')) !!}
        </div>
    </div>
    <div class="col-xs-12 pagination">
        @if(Request::has('search_text'))
            {!! $spots->appends(['search_text' => Request::get('search_text')])->render() !!}
        @elseif (Request::has('filter'))
            {!! $spots->appends(Request::get('filter'))->render() !!}
        @else
            {!! $spots->render() !!}
        @endif
    </div>
</div>
@endsection