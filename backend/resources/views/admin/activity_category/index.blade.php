@extends('admin.main')

@section('content')
{!! link_to_route('admin.activity-categories.create', 'Create activity category', [], [
'class' => 'btn btn-success button-category save-spot button-my'
]) !!}
<h2>Activity categories</h2>

<div class="col-xs-12 padding-0">

    <table class="col-xs-12 padding-0">
        <thead>
        <tr>
            <th class="col-xs-7">Type</th>
            <th class="col-xs-4">Image</th>
            <th class="col-xs-1"></th>
        </tr>
        </thead>
        <tbody>
        @foreach($categories as $category)
        <tr>
            <td>{!! link_to_route('admin.activity-categories.edit', $category->display_name, [$category->id]) !!}</td>
            <td><p> <img src="{{ $category->icon_url }}"></p></td>
            <td>{!! link_delete(route('admin.activity-categories.destroy', [$category->id]), '', ['class' => 'delete']) !!}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <div class="col-xs-12 pagination">
        {!! $categories->render() !!}
    </div>
</div>
@endsection
