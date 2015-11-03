@extends('admin.main')

@section('content')
<div class="editing col-xs-12">
    <h2>Users</h2>
    <hr>
    <form method="post" action="#" class="search-form">
        <input type="text" placeholder="Start typing...">
        <input type="submit" value="Search">
    </form>
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
                    <a htef="#" class="delete"></a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="col-xs-12 pagination">
        {!! $users->render() !!}
    </div>
</div>
@endsection