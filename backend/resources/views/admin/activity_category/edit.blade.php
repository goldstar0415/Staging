@extends('admin.main')

@section('content')
    {!! Form::model($category, [
        'method' => 'PUT',
        'route' => ['admin.activity-categories.update', $category->id],
        'class' => 'activity-category-name',
        'files' => true
    ]) !!}
    @include('admin.activity_category.form')
    {!! Form::close() !!}
@endsection