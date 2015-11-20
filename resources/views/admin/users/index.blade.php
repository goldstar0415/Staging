@extends('admin.main')

@section('content')
<div class="editing col-xs-12">
    <h2>Users</h2>
    <hr>
    {!! Form::open(['method' => 'GET', 'route' => 'admin.users.search', 'class' => 'search-form']) !!}
    {!! Form::text('search_text', null, ['placeholder' => 'Search by name']) !!}
    {!! Form::submit('Search') !!}
    {!! Form::close() !!}
    <table class="col-xs-12">
        <thead>
        <tr>
            <th class="col-sm-3">User name</th>
            <th class="col-sm-3">Email</th>
            <th class="col-sm-2">Roles</th>
            <th class="col-sm-3">Registration</th>
            <th class="col-sm-1"></th>
        </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{!! link_to_route('admin.users.show', $user->first_name . ' ' . $user->last_name, $user->id) !!}</td>
                <td>{!! link_to_route('admin.users.show', $user->email, $user->id) !!}</td>
                <td><i>
                    @foreach($user->roles as $role)
                       {{ $role->display_name }}
                    @endforeach
                </i></td>
                <td>{{ $user->created_at }}</td>
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