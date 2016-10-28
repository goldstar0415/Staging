@extends('admin.main')

@section('content')
<h2 class="col-xs-12">Hotel "{{ $restaurant->title }}" edit (Spot ID: {{ $restaurant->id }})</h2>
<hr class="col-xs-12" />
<div class="clearfix"></div>
<div class="row actions">
    {!! Form::open(['method' => 'POST', 'route' => ['admin.restaurants.post-edit', $restaurant->id], 'class' => 'edit-form']) !!}
    
    <fieldset class="row col-xs-12">
        <legend>Spot attributes</legend>
        <div class="form-group clearfix">
            <div class="col-sm-3">
                <label >remote_id: </label>
            </div>
            <div class="col-sm-9">
                <input class="form-control" disabled type="text" value="{{ $restaurant->restaurant->remote_id }}">
            </div>
        </div>
        @foreach($spotFields as $field)
        <div class="form-group clearfix">
            <div class="col-sm-3">
                <label >{{ $field }}: </label>
            </div>
            <div class="col-sm-9">
                @if(!is_array($restaurant->$field))
                <input class="form-control" required type="text" name="{{ $field }}" value="{{ $restaurant->$field }}">
                @else
                <?php $spotField = $restaurant->$field; ?>
                <input class="form-control" multiple type="text" name="{{ $field }}[]" value="{{ $spotField[0] }}">
                @endif
            </div>
        </div>
        @endforeach
    </fieldset>
    
    <fieldset class="row col-xs-12">
        <legend>Restaurant attributes</legend>
        @foreach($restaurantFields as $field)
        <div class="form-group clearfix">
            <div class="col-sm-3">
                <label >{{ $field }}: </label>
            </div>
            <div class="col-sm-9">
                @if(!is_array($restaurant->restaurant->$field))
                <input class="form-control" type="text" name="{{ $field }}" value="{{ $restaurant->restaurant->$field }}">
                @else
                <?php $restaurantField = $restaurant->restaurant->$field; ?>
                <input class="form-control" multiple type="text" name="{{ $field }}[]" value="{{ $restaurantField[0] }}">
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