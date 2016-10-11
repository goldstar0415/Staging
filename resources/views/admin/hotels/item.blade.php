@extends('admin.main')

@section('content')
<h2 class="col-xs-12">Hotel "{{ $hotel->title }}" edit (Spot ID: {{ $hotel->id }})</h2>
<hr class="col-xs-12" />
<div class="clearfix"></div>
<div class="row actions">
    {!! Form::open(['method' => 'POST', 'route' => ['admin.hotels.post-edit', $hotel->id], 'class' => 'edit-form']) !!}
    
    <fieldset class="row col-xs-12">
        <legend>Spot attributes</legend>
        <div class="form-group clearfix">
            <div class="col-sm-3">
                <label >booking_id: </label>
            </div>
            <div class="col-sm-9">
                <input class="form-control" disabled type="text" value="{{ $hotel->hotel->booking_id }}">
            </div>
        </div>
        @foreach($spotFields as $field)
        <div class="form-group clearfix">
            <div class="col-sm-3">
                <label >{{ $field }}: </label>
            </div>
            <div class="col-sm-9">
                @if(!is_array($hotel->$field))
                <input class="form-control" required type="text" name="{{ $field }}" value="{{ $hotel->$field }}">
                @else
                <input class="form-control" multiple type="text" name="{{ $field }}[]" value="{{ ($hotel->$field)[0] }}">
                @endif
            </div>
        </div>
        @endforeach
    </fieldset>
    
    <fieldset class="row col-xs-12">
        <legend>Hotel attributes</legend>
        @foreach($hotelFields as $field)
        <div class="form-group clearfix">
            <div class="col-sm-3">
                <label >{{ $field }}: </label>
            </div>
            <div class="col-sm-9">
                @if(!is_array($hotel->hotel->$field))
                <input class="form-control" type="text" name="{{ $field }}" value="{{ $hotel->hotel->$field }}">
                @else
                <input class="form-control" multiple type="text" name="{{ $field }}[]" value="{{ ($hotel->hotel->$field)[0] }}">
                @endif
            </div>
        </div>
        @endforeach
    </fieldset>
    
    
    <div class="row col-sm-offset-3 col-sm-7">
        <button class="btn btn-default" onclick="window.history.back();">Cancel</button>
        <button class="btn btn-success" type="submit">Save</button>
    </div>
    {!! Form::close() !!}  
</div>

@endsection