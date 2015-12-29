<p>
    {!! Form::label('title', 'Title:') !!}
    {!! Form::text('title', null, ['class' => 'edit-data']) !!}
</p>
<p>
    {!! Form::label('blog_category_id', 'Category: ') !!}
    {!! Form::select('blog_category_id', \App\BlogCategory::all()->pluck('display_name', 'id'), null) !!}
</p>
<p>
    {!! Form::label('main', 'Main') !!}
    {!! Form::checkbox('main', 1, null, ['class' => 'ban-check']) !!}
</p>
<p>
    {!! Form::label('cover', 'Cover') !!}
    {!! Form::file('cover') !!}
</p>
<p>
    {!! Form::label('slug', 'URL: ') !!}
    {!! Form::text('slug', null, ['class' => 'edit-data']) !!}
</p>
<p>
    {!! Form::label('location', 'Location:') !!}
    {!! Form::select(
        'address',
        [$blog->address ?: old('address') => $blog->address ?: old('address')],
        $blog->address ?: old('address'),
        ['class' => 'edit-data', 'id' => 'location']
    ) !!}
    {!! Form::hidden('location[lat]', $blog->point['lat'], ['id' => 'location_lat']) !!}
    {!! Form::hidden('location[lng]', $blog->point['lng'], ['id' => 'location_lng']) !!}
</p>
<p>
    {!! Form::textarea('body', null, ['class' => 'ckeditor']) !!}
</p>
{!! Form::submit('Save', ['class' => 'btn btn-success']) !!}