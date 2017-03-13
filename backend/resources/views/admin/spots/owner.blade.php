@extends('admin.main')

@section('content')
    <h2>
        Spot owner requests
    </h2>
    <hr>
    <table class="col-xs-12">
        <thead>
        <tr>
            <th>Author</th>
            <th>Spot</th>
            <th>Name</th>
            <th>E-mail</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Url</th>
            <th>Text</th>
            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($requests as $request)
            <tr>
                <td>{!! link_to(frontend_url($request->user_id), $request->user->full_name) !!}</td>
                <td>{!! link_to(frontend_url($request->user->id, 'spot', $request->spot->id), $request->spot->title) !!}</td>
                <td>{{ $request->name }}</td>
                <td>{{ $request->email }}</td>
                <td>{{ $request->phone }}</td>
                <td>{{ $request->address }}</td>
                <td>{{ $request->url }}</td>
                <td>{!! nl2br(strip_tags($request->text, '<br>')) !!}</td>
                <td>
                    {!! link_to_route('admin.spot-owner.accept', 'Accept', [$request->id], ['class' => 'btn btn-success button-my']) !!}
                </td>
                <td>
                    {!! link_to_route('admin.spot-owner.reject', 'Reject', [$request->id], ['class' => 'btn btn-danger button-my']) !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @include('admin.pagination', ['paginatable' => $requests])
@endsection