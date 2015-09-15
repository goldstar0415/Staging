User {{ $sender->first_name }} {{ $sender->last_name }}
coming spot: {{ $spot->title }}
{{ frontend_url('spots', 'user', $spot->user->id, 'spots', $spot->id) }}