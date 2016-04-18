<legend>
    {!! Form::submit('Save', ['class' => 'btn btn-success save-spot button-my']) !!}
    <h2>Spot category name</h2>
</legend>
<div class="col-xs-12 padding-0">
    <div class="col-xs-2">
        {!! Form::text('name', null, ['class' => 'spot-categories-name', 'placeholder' => 'Slug name']) !!}
    </div>
    <div class="col-xs-3">
        {!! Form::text('display_name', null, ['class' => 'spot-categories-name', 'placeholder' => 'Display name']) !!}
    </div>
    <div class="col-xs-3">
        {!! Form::select('spot_type_id', App\SpotType::all()->pluck('display_name', 'id'), $type, [
            'class' => 'category'
        ]) !!}
    </div>
    <div class="col-xs-4">
        {{--<a href="#" class="btn btn-primary button-my"> Upload new image</a>--}}
        {!! Form::file('icon', ['class' => '']) !!}
    </div>
</div>