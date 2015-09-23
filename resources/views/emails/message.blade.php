You have new message on <a href="{{ frontend_url() }}">Zoomtivity</a> from {{ $sender->first_name }} {{ $sender->last_name }}.
<a href="{{ frontend_url('user', $receiver->id) }}">See message</a>
