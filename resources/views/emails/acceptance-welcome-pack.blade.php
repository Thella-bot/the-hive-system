<x-mail::message>
# Congratulations, {{ $student->name }}

You have been admitted to **Honey Bee Culinary Institute**. Welcome to The Hive.

## Admission Details

**Application Reference:** HBCI-{{ str_pad($application->id, 5, '0', STR_PAD_LEFT) }}  
**Programme:** {{ $application->programme?->name ?? 'To be confirmed' }}  
@if($application->variant)
**Study Option:** {{ $application->variant->label }}  
**Duration:** {{ $application->variant->duration ?? 'To be confirmed' }}  
@elseif($application->programme?->duration)
**Duration:** {{ $application->programme->duration }}  
@endif
**Status:** Approved  
**Email:** {{ $student->email }}
@if(! empty($student->student_number))
**Student Number:** {{ $student->student_number }}
@endif

## Your Welcome Pack

Your attached acceptance letter confirms your admission. Please keep it for registration and onboarding.

Your next steps:

1. Set your password and log in to The Hive.
2. Review your profile details.
3. Watch for orientation, registration, uniform, fees, and timetable updates from the institute.
4. Bring your acceptance letter and identification when requested by Admissions.

<x-mail::button :url="$passwordResetUrl">
Set Password & Access The Hive
</x-mail::button>

If the button does not work, paste this link into your browser:

{{ $passwordResetUrl }}

We are excited to welcome you to the Honey Bee Culinary Institute community.

Thanks,<br>
Admissions Team<br>
{{ config('app.name') }}
</x-mail::message>
