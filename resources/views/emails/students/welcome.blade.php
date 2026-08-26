<x-mail::message>
# Welcome to {{ config('app.name') }},

Dear {{ $userName }},

Your account has been created successfully. You can now log in to access your student dashboard.

**Login Details:**
- **Email:** {{ $userEmail }}
@if($password)
- **Temporary Password:** {{ $password }}
@endif

<x-mail::button :url="$loginUrl">
Log In Now
</x-mail::button>

Please change your password after your first login for security purposes.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
