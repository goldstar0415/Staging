@extends('admin.main')

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => 'admin.posts.store', 'class' => 'new-blog']) !!}
    @include('admin.blog.form')
    {!! Form::close() !!}
@endsection
