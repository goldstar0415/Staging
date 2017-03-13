@extends('admin.main')

@section('content')
    <h2>Contact Us Requests</h2>
    <hr>
    {!! Form::open(['method' => 'GET', 'route' => 'admin.contact-us.search', 'class' => 'search-form']) !!}
    {!! Form::text('search_text', null, ['placeholder' => 'Search by name']) !!}
    {!! Form::submit('Search') !!}
    {!! Form::close() !!}
    <table class="col-xs-12">
        <thead>
        <tr>
            <th class="col-sm-2">User name</th>
            <th class="col-sm-2">Email</th>
            <th class="col-sm-2">Requests date</th>
            <th class="col-sm-5">Request text</th>
            <th class="col-sm-1"></th>
        </tr>
        </thead>
        <tbody>
        @foreach($contacts as $contact)
            <tr>
                <td>{{ $contact->username }}</td>
                <td>{!! link_to('mailto:' . $contact->email, $contact->email) !!}</td>

                <td>{{ $contact->created_at }}</td>
                <td>
                    {!! strip_tags(nl2br($contact->message), '<br>') !!}
                </td>
                <td>
                    {!! link_delete(route('admin.contact-us.destroy', [$contact->id]), '', ['class' => 'delete']) !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="col-xs-12 pagination">
        @if(Request::has('search_text'))
            {!! $contacts->appends(['search_text' => Request::get('search_text')])->render() !!}
        @else
            {!! $contacts->render() !!}
        @endif
    </div>
@endsection