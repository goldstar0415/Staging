@extends('admin.main')

@section('content')
    <h2>Spot requests</h2>
    <hr>
    {!! Form::open(['method' => 'POST', 'class' => 'search-form']) !!}
    {!! Form::text('text', null, ['placeholder' => 'Search by name']) !!}
    {!! Form::submit('Search') !!}
    {!! Form::close() !!}
    <table class="col-xs-12 requests">
        <thead>
        <tr>
            <th class="col-sm-3">Title</th>
            <th class="col-sm-5">Description</th>
            <th class="col-sm-2"></th>
            <th class="col-sm-2"></th>
        </tr>
        </thead>
        <tbody>
        @foreach($spots as $spot)
            <tr>
                <td>
                    <a href="#">{{ $spot->title }}</a>
                </td>
                <td>
                    {{ $spot->description }}
                </td>
                <td>
                    {!! link_to_route('admin.spot-requests.approve', 'Approve', [$spot->id], ['class' => 'btn btn-success button-my']) !!}
                </td>
                <td>
                    {!! link_to_route('admin.spot-requests.reject', 'Reject', [$spot->id], ['class' => 'btn btn-danger button-my']) !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="col-xs-12 pagination">
        {!! $spots->render() !!}
    </div>
@endsection