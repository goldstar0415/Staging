@extends('admin.main')

@section('content')
{!! link_to_route('admin.spot-categories.create', 'Create spot category', [], [
'class' => 'btn btn-success button-category creat-spot button-my'
]) !!}
<h2>Spot categories</h2>
<div class="col-xs-12 padding-0">
    <table class="col-xs-12 padding-0">
        <thead>
        <tr>
            <th class="col-xs-5">Category</th>
            <th class="col-xs-3">Type</th>
            <th class="col-xs-3">Image</th>
            <th class="col-xs-1"></th>
        </tr>
        </thead>
        <tbody>
        @foreach($categories as $category)
        <tr>
            <td>{!! link_to_route('admin.spot-categories.edit', $category->display_name, [$category->id]) !!}</td>
            <td><p>{{ $category->type['display_name'] }}</p></td>
            <td><p> <img src="{{ $category->icon_url }}"></p></td>
            <td>{!! link_delete(route('admin.spot-categories.destroy', [$category->id]), '', ['class' => 'delete']) !!}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="col-xs-12 pagination">
        {!! $categories->render() !!}
    </div>
</div>
@endsection
