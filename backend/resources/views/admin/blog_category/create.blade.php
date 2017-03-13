@extends('admin.main')

@section('content')
<h2>
    Blog categories
    {!! link_to_route('admin.blog-categories.index', 'Categories', [], ['class' => 'btn btn-primary button-my right']) !!}
</h2>
<hr>
{!! Form::open([
    'method' => 'POST',
    'route' => 'admin.blog-categories.store',
    'class' => 'creat-activity-level'
]) !!}
@include('admin.blog_category.form')
{!! Form::close() !!}
@endsection