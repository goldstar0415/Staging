Hi, {{ $user->full_name }}
Change email link {{ url('/email-change/' . $token) }}