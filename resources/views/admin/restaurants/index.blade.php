@extends('admin.main')

@section('content')
<div class="editing col-xs-12">
    <h2>Restaurants</h2>
    <hr>
    <div class="row actions">
        {!! Form::open(['method' => 'GET', 'route' => 'admin.restaurants.filter', 'class' => 'form-inline']) !!}
        <div class="form-group">
            {!! Form::label('filter[title]', 'Title:') !!}
            {!! Form::text('filter[title]', old('filter.title'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[description]', 'Description:') !!}
            {!! Form::text('filter[description]', old('filter.description'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('filter[created_at]', 'Created at:') !!}
            {!! Form::text('filter[created_at]', old('filter.created_at'), ['class' => 'form-control']) !!}
        </div>
        
        {!! Form::button('Filter', ['class' => 'btn btn-default', 'type' => 'submit']) !!}
        {!! Form::close() !!}
        {!! Form::open(['method' => 'POST', 'route' => 'admin.restaurants.clean-db', 'class' => 'form-for-trunkate']) !!}
        {!! Form::close() !!}
    </div>
    <table class="col-xs-12">
        <thead>
        <tr>
            <th class="col-sm-1" id="bulk"><input type="checkbox"></th>
            <th class="col-sm-2">Title</th>
            <th class="col-sm-3">Description</th>
            <th class="col-sm-1">Create Date</th>
            <th class="col-sm-1"></th>
        </tr>
        </thead>
        <tbody>
        @foreach($restaurants as $restaurant)
            <tr>
                <td class="text-center">{!! Form::checkbox('spots[]', $restaurant->id, null, ['class' => 'row-select']) !!}</td>
                <td>{!! link_to(frontend_url( 'restaurant', $restaurant->id), $restaurant->title, ['target' => '_blank']) !!}</td>
                <td>{{ $restaurant->description }}</td>
                <td>{{ $restaurant->created_at->format('Y-m-d') }}</td>
                <td>
                    <a href="{!! route('admin.restaurants.get-edit', [$restaurant->id]) !!}" class="edit-spot"></a>
                    {!! link_delete(route('admin.restaurants.destroy', [$restaurant->id]), '', ['class' => 'delete']) !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="clearfix"></div>
<div class="row actions col-lg-12">
    <div class="form-group pull-left">
        {!! link_to_route('admin.restaurants.bulk-delete', 'Delete selected', [], ['id' => 'bulk-delete', 'class' => 'btn btn-danger']) !!}
    </div>
    <div class="form-group pull-left">
        <a href="javascript:void(0);" class="clean-btn btn btn-danger">Clean Restaurants Database</a>
    </div>
    <div class="form-group pull-right">
        {!! Form::label('limit', 'Items per page') !!}
        {!! Form::select('limit', [15 => '15', 50 => '50', 100 => '100'], Request::get('limit')) !!}
    </div>
</div>
@include('admin.pagination', ['paginatable' => $restaurants])
@endsection

@section('scripts')
<script>
$(function(){
    $('.clean-btn').on('click', function(e){
        e.preventDefault();
        if(confirm('Do you really want to clean restaurants database?!'))
        {
            $('.form-for-trunkate').submit();
        }
        
    });
});
</script>
@endsection