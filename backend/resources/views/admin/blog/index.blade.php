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
        <th class="col-sm-3">Author</th>
        <th class="col-sm-5">Post</th>
        <th class="col-sm-3">Date</th>
        <th class="col-sm-1"></th>
    </tr>
    </thead>
    <tbody>
    @foreach($blogs as $blog)
        <tr>
            <td>{!! link_to(frontend_url($blog->user_id), $blog->user->first_name . ' ' . $blog->user->last_name) !!}</td>
            <td>{!! link_to_route('admin.posts.edit', $blog->title, [$blog->slug]) !!}</td>
            <td>{{ $blog->created_at }}</td>
            <td>
                {!! link_delete(route('admin.posts.destroy', [$blog->id]), '', ['class' => 'delete']) !!}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@include('admin.pagination', ['paginatable' => $blogs])
@endsection