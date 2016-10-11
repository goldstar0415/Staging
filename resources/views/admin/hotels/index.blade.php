@extends('admin.main')

@section('content')
<div class="editing col-xs-12">
    <h2>Hotels</h2>
    <hr>
    <div class="row actions">
        {!! Form::open(['method' => 'GET', 'route' => 'admin.hotels.filter', 'class' => 'form-inline']) !!}
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
        <div class="form-group">
            <a href="javascript:void(0);" class="clean-btn btn btn-danger">Clean Hotels Database</a>
        </div>
        {!! Form::close() !!}
        {!! Form::open(['method' => 'DELETE', 'route' => 'admin.hotels.clean-db', 'class' => 'form-for-trunkate']) !!}
        {!! Form::close() !!}
    </div>
    <table class="col-xs-12">
        <thead>
        <tr>
            <th class="col-sm-1" id="bulk"><input type="checkbox"></th>
            <th class="col-sm-2">Title</th>
            <th class="col-sm-3">Description</th>
            <th class="col-sm-1">Create Date</th>
            <th class="col-sm-2">Hotels.com URL</th>
            <th class="col-sm-2">Booking.com URL</th>
            <th class="col-sm-1"></th>
        </tr>
        </thead>
        <tbody>
        @foreach($hotels as $hotel)
            <tr>
                <td>{!! Form::checkbox('hotels[]', $hotel->id, null, ['class' => 'row-select']) !!}</td>
                <td>{!! link_to(frontend_url( 'hotel', $hotel->id), $hotel->title) !!}</td>
                <td>{{ $hotel->desc_en }}</td>
                <td>{{ $hotel->created_at->format('Y-m-d') }}</td>
                <td><a href="{{ url($hotel->hotel->hotelscom_url) }}">hotels.com</a></td>
                <td><a href="{{ url($hotel->hotel->booking_url) }}">booking.com</a></td>
                <td>
                    {!! link_delete(route('admin.hotels.destroy', [$hotel->id]), '', ['class' => 'delete']) !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="row actions col-lg-12">
    <div class="form-group pull-right">
        {!! Form::label('limit', 'Items per page') !!}
        {!! Form::select('limit', [15 => '15', 50 => '50', 100 => '100'], Request::get('limit')) !!}
    </div>
</div>
@include('admin.pagination', ['paginatable' => $hotels])
@endsection

@section('scripts')
<script>
$(function(){
    $('.clean-btn').on('click', function(e){
        e.preventDefault();
        
        if(confirm('Do you really want to clean hotels database?!'))
        {
            $('.form-for-trunkate').submit();
        }
        
    });
});
</script>
@endsection