@extends('admin.main')

@section('content')
<h2>
    Send email letter
</h2>
<hr>
<form action="#" method="POST" class="mail-form">
    <div class="form-group">
        <label for="users">Receivers</label>
        <select name="users[]" id="users" class="form-control" multiple></select>
    </div>
    <div class="form-group">
        <label for="subject">Subject</label>
        <input name="subject" id="subject" type="text" class="form-control">
    </div>
    <div class="form-group">
        <label for="body">Subject</label>
        <textarea name="body" id="body" cols="30" rows="10" class="form-control"></textarea>
    </div>
    <input type="submit" value="Send" class="btn btn-default form-control">
</form>
@endsection