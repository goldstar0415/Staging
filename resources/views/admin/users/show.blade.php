<div class="user-data clearfix">
    <div class="col-sm-12">
        <a href="admin_edit_user_page.html" class="btn btn-success button-my">Edit</a>
        <a href="#" class="btn btn-danger button-my">Delete</a>

        <div class="col-sm-2  ">
            <img src="/assets/img/icons/avatar.jpg">
        </div>
        <div class="col-sm-10">
            <h3>{{ $user->first_name . ' ' . $user->last_name }}</h3>

            <p><span class="">e-mail:</span><a href="#">{{ $user->email }}</a></p>

            <p>
                <span>Roles:</span>
                @foreach($user->roles as $role)
                    {{ $role->display_name }}
                @endforeach
            </p>

            <p><span>Registration:</span>{{ $user->created_at }}</p>
        </div>
    </div>
</div>
<div class=" ">
    <hr>
    <form method="post" action="#" class="search-form">
        <input type="text" placeholder="Start typing...">
        <input type="submit" value="Search">
    </form>
    <table class="col-xs-12">
        <thead>
        <tr>
            <th class="col-xs-4">Spots</th>
            <th class="col-xs-3">Spot Type</th>
            <th class="col-xs-3">Spot category</th>
            <th class="col-xs-1"></th>
            <th class="col-xs-1"></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><a href="#">Festival </a></td>
            <td><p>Event</p></td>
            <td><p><img src="/assets/img/img10.jpg"> casino</p></td>
            <td><a href="#" class="delete"></a></td>
            <td><a href="#" class="edit-spot"></a></td>
        </tr>
        <tr>
            <td><a href="#">Festival </a></td>
            <td><p>Recreation</p></td>
            <td><p><img src="/assets/img/img10.jpg"> casino</p></td>
            <td><a href="#" class="delete"></a></td>
            <td><a href="#" class="edit-spot"></a></td>
        </tr>
        <tr>
            <td><a href="#">Festival </a></td>
            <td><p>Pit stop</p></td>
            <td><p><img src="/assets/img/img10.jpg"> casino</p></td>
            <td><a href="#" class="delete"></a></td>
            <td><a href="#" class="edit-spot"></a></td>
        </tr>
        <tr>
            <td><a href="#">Festival </a></td>
            <td><p>Recreation</p></td>
            <td><p><img src="/assets/img/img10.jpg"> casino</p></td>
            <td><a href="#" class="delete"></a></td>
            <td><a href="#" class="edit-spot"></a></td>
        </tr>
        <tr>
            <td><a href="#">Festival Festival Festi Festi</a></td>
            <td><p>Event</p></td>
            <td><p><img src="/assets/img/img10.jpg"> casino</p></td>
            <td><a href="#" class="delete"></a></td>
            <td><a href="#" class="edit-spot"></a></td>
        </tr>
        <tr>
            <td><a href="#">Festival </a></td>
            <td><p>Event</p></td>
            <td><p><img src="/assets/img/img10.jpg"> casino</p></td>
            <td><a href="#" class="delete"></a></td>
            <td><a href="#" class="edit-spot"></a></td>
        </tr>
        <tr>
            <td><a href="#">Festival </a></td>
            <td><p>Event</p></td>
            <td><p><img src="/assets/img/img10.jpg"> casino</p></td>
            <td><a href="#" class="delete"></a></td>
            <td><a href="#" class="edit-spot"></a></td>
        </tr>

        </tbody>
    </table>
    <div class="col-xs-12 pagination">
    </div>
</div>