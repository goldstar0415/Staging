<div class="col-md-2 col-xs-4 admin-menu">
    <ul>
        <li>{!! link_to_route('admin.posts.index', 'Blog') !!}
        </li>
        <li><a href="admin_about_us.html">About us</a>
        </li>
        <li>{!! link_to_route('admin.users.index', 'Users') !!}
        </li>
        <li>{!! link_to_route('admin.spot-categories.index', 'Spot Categories') !!}
        </li>
        <li>{!! link_to_route('admin.activity-categories.index', 'Activity categories') !!}
        </li>
        <li>{!! link_to_route('admin.activitylevel.index', 'User type') !!}
        </li>
        <li>{!! link_to_route('admin.contact-us.index', 'Contact Us requests') !!}
        </li>
        <li>
            {!! link_to_route('admin.spot-requests.index', 'Spot requests') !!}
        </li>
        <li>
            {!! link_to_route('admin.blogger-requests.index', 'Blogger requests') !!}
        </li>
        <li>{!! link_to_route('admin.spot-import', 'Parse CSV') !!}
        </li>
    </ul>
</div>
