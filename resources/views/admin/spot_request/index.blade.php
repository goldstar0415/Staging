@extends('admin.main')

@section('content')
    <h2>Spot requests</h2>
    <hr>
    {!! Form::open(['method' => 'GET', 'route' => 'admin.spot-requests.search','class' => 'search-form']) !!}
    {!! Form::text('search_text', null, ['placeholder' => 'Search by name']) !!}
    {!! Form::submit('Search') !!}
    {!! Form::close() !!}
    <table class="col-xs-12 requests">
        <thead>
        <tr>
            <th class="col-sm-2">Username</th>
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
                    @if ($spot->hasOwner())
                        <a href="{!! frontend_url($spot->user->id) !!}">
                            {{ $spot->user->first_name . ' ' . $spot->user->last_name }}
                        </a>
                    @else
                        No owner
                    @endif
                </td>
                <td>
                {!! link_to(frontend_url($spot->user_id ?: Request::user()->id, 'spot', $spot->id, $spot->slug), $spot->title) !!}
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
    @include('admin.pagination', ['paginatable' => $spots])
@endsection