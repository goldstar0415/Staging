Your friend {{ $sender->first_name }} {{ $sender->last_name }} has new event
<a href="{{ frontend_url('spots', 'user', $spot->user->id, 'spots', $spot->id) }}">{{ $spot->title }}</a>.