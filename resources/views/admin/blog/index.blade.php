@extends('admin.main')

@section('content')
<h2>
    Blog {!! link_to_route('admin.posts.create', 'New', [], ['class' => 'btn btn-success button-my right']) !!}
    <a href="admin_blog_categories.html" class="btn btn-primary button-my right">Categories</a>
</h2>
<hr>
{!! Form::open(['method' => 'POST', 'class' => 'search-form']) !!}
{!! Form::text('text', null, ['placeholder' => 'Search by name']) !!}
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
    {!! $blogs->render() !!}
</div>
@endsection