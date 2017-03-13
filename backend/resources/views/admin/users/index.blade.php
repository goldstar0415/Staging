@extends('admin.main')

@section('content')
<div class="editing col-xs-12">
    <h2>Users</h2>
    <hr>
    <div class="row actions">
        <ul class="nav nav-pills">
            <li role="presentation">
                {!! link_to_route('admin.email', 'Email', [], ['id' => 'email-users']) !!}
            </li>
            <li role="presentation">
                {!! link_to_route('admin.users.bulk-delete', 'Delete', [], ['id' => 'bulk-delete']) !!}
            </li>
        </ul>
    </div>
    {!! Form::open(['method' => 'GET', 'route' => 'admin.users.search', 'class' => 'search-form']) !!}
    {!! Form::text('search_text', null, ['placeholder' => 'Search by name']) !!}
    {!! Form::submit('Search') !!}
    {!! Form::close() !!}
    <table class="col-xs-12">
        <thead>
        <tr>
            <th id="bulk"><input type="checkbox"></th>
            <th>User name</th>
            <th>Email</th>
            <th>Roles</th>
            <th>Registration</th>
            <th><a href="/admin/users?sort=ip">IP address</a></th>
            <th>Location</th>
            <th>Geo location</th>
            <th>Last login</th>
            <th>Count of reviews</th>
            <th>Count of spots</th>
            <th>Count of followers</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{!! Form::checkbox('users[]', $user->id, null, ['class' => 'row-select']) !!}</td>
                <td>{!! link_to_route('admin.users.show', $user->first_name . ' ' . $user->last_name, $user->id) !!}</td>
                <td>{!! link_to_route('admin.users.show', $user->email, $user->id) !!}</td>
                <td><i>
                    @foreach($user->roles as $role)
                       {{ $role->display_name }}
                    @endforeach
                </i></td>
                <td>{{ $user->created_at }}</td>
                <td>{{ $user->ip }}</td>
                <td>{{ $user->address }}</td>
                <td>
                    {{ $user->geo_location }}
                </td>
                <td>{{ $user->last_action_at }}</td>
                <td>{{ $user->count_reviews }}</td>
                <td>{{ $user->count_spots }}</td>
                <td>{{ $user->count_followers }}</td>
                <td>
                    {!! link_delete(route('admin.users.destroy', [$user->id]), '', ['class' => 'delete']) !!}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @include('admin.pagination', ['paginatable' => $users])
</div>
@endsection