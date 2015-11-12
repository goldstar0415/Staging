<legend>
    {!! Form::submit('Save', ['class' => 'btn btn-success  button-my right']) !!}
    <h2>Activity category name</h2>
</legend>
<div class="col-xs-12 padding-0">
    <div class="col-xs-8">
        {!! Form::text('display_name', null, ['placeholder' => 'New-name']) !!}
    </div>
    <div class="col-xs-4">
        {!! Form::file('icon', ['class' => '']) !!}
    </div>
</div>