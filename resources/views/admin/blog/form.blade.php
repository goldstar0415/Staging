<p>
    {!! Form::label('title', 'Title:') !!}
    {!! Form::text('title', null, ['class' => 'edit-data']) !!}
</p>
<p>
    {!! Form::label('category_id', 'Category: ') !!}
    {!! Form::select('category_id', \App\BlogCategory::all()->pluck('display_name', 'id'), null, ['size' => 4]) !!}
</p>
<p>
    {!! Form::label('is_main', 'Main') !!}
    {!! Form::checkbox('is_main', 1, null, ['class' => 'ban-check']) !!}
</p>
<p><a href="#" class="btn btn-primary "> Upload new image</a></p>
<p>
    {!! Form::label('url', 'URL: ') !!}
    {!! Form::text('url', null, ['class' => 'edit-data']) !!}
</p>
<p>
    {!! Form::label('location', 'Location:') !!}
    {!! Form::text('location', null, ['class' => 'edit-data']) !!}
</p>
<p>
    {!! Form::textarea('body') !!}
</p>
{!! Form::submit('Save', ['class' => 'btn btn-success']) !!}