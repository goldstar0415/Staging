Zoomtivity generated user reminder
password: {{ $password }}

Please verify your account by following this link:

{{ url('/users/confirm/' . $user->token) }}