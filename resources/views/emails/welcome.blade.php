<x-mail::message>
# Welcome to The Hive, {{ $user->name }}!

Your account has been created successfully. You can now log in to The Hive using the following credentials:

**Email:** {{ $user->email }}
**Password:** {{ $password }}

For security reasons, we strongly recommend that you change your password after your first login.

You can access The Hive at the following URL:
<x-mail::button :url="config('app.url')">
Login to The Hive
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
