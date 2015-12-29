@extends('admin.main')

@section('content')
{!! Form::model($blog, ['method' => 'PUT', 'route' => ['admin.posts.update', $blog->id], 'class' => 'new-blog', 'files' => true]) !!}
    @include('admin.blog.form')
{!! Form::close() !!}
@endsection
