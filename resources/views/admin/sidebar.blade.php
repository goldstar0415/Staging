<div class="container wrap">
    <div class="col-md-3 col-xs-4 admin-menu">
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
            <li><a href="admin_contact_us_requests.html">Contact us requests</a>
            </li>

            <li><a href="admin_reaction_requests.html">Recreation requests</a>
            </li>
            <li><a href="admin_pit_stop_requests.html">Pit stop requests</a>
            </li>

            <li><a href="admin_blogger_reguests.html">Bloggers requests</a>
            </li>
            <li>{!! link_to_route('admin.spot-import', 'Parse CSV') !!}
            </li>
        </ul>
    </div>
</div>
