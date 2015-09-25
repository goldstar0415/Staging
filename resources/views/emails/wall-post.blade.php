You have new message on the wall from {{ $sender->first_name }} {{ $sender->last_name }}.
<a href="{{ frontend_url('user', $wall->receiver_id) }}">See message</a>