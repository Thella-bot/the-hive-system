<x-mail::message>
# Welcome to The Hive, {{ $user->name }}!

Your account has been created successfully. You can now log in to The Hive using your email address.

**Email:** {{ $user->email }}

For security reasons, we recommend that you set a strong password after logging in.

You can access The Hive at the following URL:
<x-mail::button :url="config('app.url')">
Login to The Hive
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
