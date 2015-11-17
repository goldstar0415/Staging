@extends('admin.main')

@section('content')
<h2>
    Send email letter
</h2>
<hr>
<form action="#" class="mail-form">
    <div class="form-group">
        <label for="users">Receivers</label>
        <select name="users" id="users" class="form-control" multiple>
            <option value="1">John</option>
            <option value="2">Jaack</option>
            <option value="3">miss</option>
            <option value="4">Lorea</option>
            <option value="5">Some</option>
        </select>
    </div>
    <div class="form-group">
        <label for="subject">Subject</label>
        <input name="subject" id="subject" type="text" class="form-control">
    </div>
    <div class="form-group">
        <label for="body">Subject</label>
        <textarea name="body" id="body" cols="30" rows="10" class="form-control">
        </textarea>
    </div>
    <input type="submit" value="Send" class="btn btn-default form-control">
</form>
@endsection