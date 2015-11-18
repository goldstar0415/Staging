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
<p>
    {!! Form::label('cover', 'Cover') !!}
    {!! Form::file('cover') !!}
</p>
<p>
    {!! Form::label('url', 'URL: ') !!}
    {!! Form::text('url', null, ['class' => 'edit-data']) !!}
</p>
<p>
    {!! Form::label('location', 'Location:') !!}
    <select name="address" id="location" class="edit-data"></select>
    {!! Form::hidden('location[lat]', null, ['id' => 'location_lat']) !!}
    {!! Form::hidden('location[lng]', null, ['id' => 'location_lng']) !!}
</p>
<p>
    {!! Form::textarea('body', null, ['class' => 'ckeditor']) !!}
</p>
{!! Form::submit('Save', ['class' => 'btn btn-success']) !!}