@extends('admin.main')

@section('content')
{!! Form::open([
    'method' => 'POST',
    'route' => 'admin.activity-categories.store',
    'class' => 'activity-category-name',
    'files' => true
]) !!}
    @include('admin.activity_category.form')
{!! Form::close() !!}
@endsection
