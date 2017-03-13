@extends('admin.main')

@section('content')
{!! Form::open([
    'method' => 'POST',
    'route' => 'admin.spot-categories.store',
    'class' => 'spot-category-name',
    'files' => true
]) !!}
    @include('admin.spot_categories.form', ['type' => 1])
{!! Form::close() !!}
@endsection
