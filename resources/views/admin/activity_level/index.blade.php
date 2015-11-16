@extends('admin.main')

@section('content')
<!--<form action="" method="">-->
<legend>
    {!! link_to_route('admin.activitylevel.create', 'New', [], ['class' => 'btn btn-success save-spot button-my']) !!}
    <h2>Activity level</h2>
</legend>
<div class="col-xs-12 padding-0">
    <table class="col-xs-12 padding-0">
        <thead>
        <tr>
            <th class="col-xs-7">Title</th>
            <th class="col-xs-4">Quantity</th>
            <th class="col-xs-1"></th>
        </tr>
        </thead>
        <tbody>
        @foreach($levels as $level)
            <tr>
                <td>{!! link_to_route('admin.activitylevel.edit', $level->name, ['activitylevel' => $level->id]) !!}</td>
                <td><p>{{ $level->favorites_count }}</p></td>
                <td>{!! link_delete(route('admin.activitylevel.destroy', [$level->id]), '', ['class' => 'delete']) !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!--</form>-->
@endsection