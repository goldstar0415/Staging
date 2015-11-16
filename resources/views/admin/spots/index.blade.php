@extends('admin.main')

@section('content')
<div class="editing col-xs-12">
    <h2>Users</h2>
    <hr>
    {!! Form::open(['method' => 'GET', 'route' => 'admin.spots.search', 'class' => 'search-form']) !!}
    {!! Form::text('search_text', null, ['placeholder' => 'Search by name']) !!}
    {!! Form::submit('Search') !!}
    {!! Form::close() !!}
    <table class="col-xs-12">
        <thead>
        <tr>
            <th class="col-sm-3">Title</th>
            <th class="col-sm-3">Description</th>
            <th class="col-sm-2">Category</th>
            <th class="col-sm-2">Date added</th>
            <th class="col-sm-2">Event date</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($spots as $spot)
            <tr>
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
    <div class="col-xs-12 pagination">
        @if(Request::has('search_text'))
            {!! $spots->appends(['search_text' => Request::get('search_text')])->render() !!}
        @else
            {!! $spots->render() !!}
        @endif
    </div>
</div>
@endsection