@extends('admin.main')

@section('content')
    <h2>
        Blog categories
        {!! link_to_route('admin.blog-categories.create', 'New', [], ['class' => 'btn btn-success button-my right']) !!}
    </h2>
    <hr>
    <table class="col-xs-12">
        <thead>
        <tr>
            <th class="col-sm-11">Name</th>
            <th class="col-sm-1"></th>
        </tr>
        </thead>
        <tbody>
        @foreach($categories as $category)
            <tr>
                <td>{!! link_to_route('admin.blog-categories.edit', $category->display_name, [$category->id]) !!}</td>
                <td>
                    {!! link_delete(route('admin.blog-categories.destroy', [$category->id]), '', ['class' => 'delete']) !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @include('admin.pagination', ['paginatable' => $categories])
@endsection