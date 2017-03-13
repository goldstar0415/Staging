@extends('admin.main')

@section('content')
    {!! Form::open(['method' => 'POST', 'route' => 'admin.posts.store', 'class' => 'new-blog', 'files' => true]) !!}
    @include('admin.blog.form', ['blog' => new App\Blog])
    {!! Form::close() !!}
@endsection
