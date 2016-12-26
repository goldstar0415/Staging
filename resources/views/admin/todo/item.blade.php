@extends('admin.main')

@section('content')
<h2 class="col-xs-12">ToDo "{{ $todo->title }}" edit (Spot ID: {{ $todo->id }})</h2>
<hr class="col-xs-12" />
<div class="clearfix"></div>
<div class="row actions">
    {!! Form::open(['method' => 'POST', 'route' => ['admin.doto.post-edit', $todo->id], 'class' => 'edit-form']) !!}
    
    <fieldset class="row col-xs-12">
        <legend>Spot attributes</legend>
        <div class="form-group clearfix">
            <div class="col-sm-3">
                <label >booking_id: </label>
            </div>
            <div class="col-sm-9">
                <input class="form-control" disabled type="text" value="{{ $todo->todo->booking_id }}">
            </div>
        </div>
        @foreach($spotFields as $field)
        <div class="form-group clearfix">
            <div class="col-sm-3">
                <label >{{ $field }}: </label>
            </div>
            <div class="col-sm-9">
                @if(!is_array($todo->$field))
                <input class="form-control" required type="text" name="{{ $field }}" value="{{ $todo->$field }}">
                @else
                <?php $spotField = $todo->$field; ?>
                <input class="form-control" multiple type="text" name="{{ $field }}[]" value="{{ $spotField[0] }}">
                @endif
            </div>
        </div>
        @endforeach
    </fieldset>
    
    <fieldset class="row col-xs-12">
        <legend>ToDo attributes</legend>
        @foreach($todoFields as $field)
        <div class="form-group clearfix">
            <div class="col-sm-3">
                <label >{{ $field }}: </label>
            </div>
            <div class="col-sm-9">
                @if(!is_array($todo->todo->$field))
                <input class="form-control" type="text" name="{{ $field }}" value="{{ $todo->todo->$field }}">
                @else
                <?php $todoField = $todo->todo->$field; ?>
                <input class="form-control" multiple type="text" name="{{ $field }}[]" value="{{ $todoField[0] }}">
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