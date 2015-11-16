@extends('admin.main')

@section('content')
    <h2>Bloggers  Requests</h2>
    <hr>
    <table class="col-xs-12 bloggers-requests">
        <thead>
        <tr>
            <th class="col-sm-2">User name</th>
            <th class="col-sm-2">Email</th>
            <th class="col-sm-5">Request text</th>
            <th class="col-sm-3">Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($requests as $request)
            <tr>
                <td><a href="#">{{ $request->user->first_name . ' ' . $request->user->last_name }}</a></td>
                <td><a href="#">{{ $request->user->email }}</a></td>
                <td>{{ $request->text }}</td>
                <td>
                    @if($request->status == 'requested')
                        {!! link_to_route('admin.blogger-requests.accept', 'Accept', [$request->id], ['class' => 'btn btn-success button-my']) !!}
                        {!! link_to_route('admin.blogger-requests.reject', 'Reject', [$request->id], ['class' => 'btn btn-danger button-my']) !!}
                    @else
                        {{ $request->status }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="col-xs-12 pagination">
        {!! $requests->render() !!}
    </div>
@endsection