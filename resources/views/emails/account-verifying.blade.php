Hi, {{ $user->full_name }}

Please verify your account by following this link:

{{ url('/users/confirm/' . $user->token) }}