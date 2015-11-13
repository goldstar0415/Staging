@extends('admin.main')

@section('content')
<h2>
    Blog {!! link_to_route('admin.posts.create', 'New', [], ['class' => 'btn btn-success button-my right']) !!}
    {!! link_to_route('admin.blog-categories.index', 'Categories', [], ['class' => 'btn btn-primary button-my right']) !!}
</h2>
<hr>
{!! Form::open(['method' => 'GET', 'route' => 'admin.posts.search', 'class' => 'search-form']) !!}
{!! Form::text('search_text', null, ['placeholder' => 'Search by name']) !!}
{!! Form::submit('Search') !!}
{!! Form::close() !!}
<table class="col-xs-12">
    <thead>
    <tr>
        <th class="col-sm-5">Post</th>
        <th class="col-sm-5">Date</th>
        <th class="col-sm-2"></th>
    </tr>
    </thead>
    <tbody>
    @foreach($blogs as $blog)
        <tr>
            <td>{!! link_to_route('admin.posts.edit', $blog->title, [$blog->id]) !!}</td>
            <td>{{ $blog->created_at }}</td>
            <td>
                {!! link_delete(route('admin.posts.destroy', [$blog->id]), '', ['class' => 'delete']) !!}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="col-xs-12 pagination">
    @if(Request::has('search_text'))
        {!! $blogs->appends(['search_text' => Request::get('search_text')])->render() !!}
    @else
        {!! $blogs->render() !!}
    @endif
</div>
@endsection