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

Your attached acceptance letter confirms your admission to {{ $application->programme?->name ?? 'your selected programme' }}. Please keep it for your records.

Your next steps:

1. **Set your password** and log in to The Hive using the button below.
2. **Complete your registration** — upload your proof of payment to finalise your enrollment.
3. Once your registration is verified, you'll receive full access to your modules and learning materials.
4. Watch for orientation, uniform collection, and timetable updates from the institute.

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
