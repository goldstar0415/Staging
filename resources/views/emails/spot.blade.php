Your friend {{ $sender->first_name }} {{ $sender->last_name }} has new event
<a href="{{ frontend_url('user', $spot->user->id, 'spot', $spot->id) }}">{{ $spot->title }}</a>.