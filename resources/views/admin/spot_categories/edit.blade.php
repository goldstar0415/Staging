@extends('admin.main')

@section('content')
    {!! Form::model($category, [
        'method' => 'PUT',
        'route' => ['admin.spot-categories.update', $category->id],
        'class' => 'spot-category-name',
        'files' => true
    ]) !!}
    @include('admin.spot_categories.form', ['type' => $category->type->id])
    {!! Form::close() !!}
@endsection