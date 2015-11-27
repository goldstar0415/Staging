Hi, {{ $user->full_name }}
Change email link {{ url('/users/email-change/' . $token) }}