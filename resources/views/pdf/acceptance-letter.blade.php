<!DOCTYPE html>
<html>
<head>
    <title>Acceptance Letter</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; line-height: 1.55; }
        .header { border-bottom: 3px solid #d97706; padding-bottom: 16px; margin-bottom: 28px; }
        .brand { font-size: 22px; font-weight: 700; color: #92400e; }
        .muted { color: #6b7280; font-size: 12px; }
        .box { border: 1px solid #e5e7eb; padding: 14px; margin: 18px 0; }
        .label { font-weight: 700; }
        .signature { margin-top: 36px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">Honey Bee Culinary Institute</div>
        <div class="muted">Acceptance Letter | Reference HBCI-{{ str_pad($application->id, 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    <p>Dear {{ $student->name }},</p>

    <p>
        Congratulations. We are pleased to confirm that your application to Honey Bee Culinary Institute has been approved.
        We are delighted to offer you a place in the programme listed below.
    </p>

    <div class="box">
        <p><span class="label">Programme:</span> {{ $application->programme?->name ?? 'To be confirmed' }}</p>
        @if($application->variant)
            <p><span class="label">Study Option:</span> {{ $application->variant->label }}</p>
            <p><span class="label">Duration:</span> {{ $application->variant->duration ?? 'To be confirmed' }}</p>
        @elseif($application->programme?->duration)
            <p><span class="label">Duration:</span> {{ $application->programme->duration }}</p>
        @endif
        @if(! empty($student->student_number))
            <p><span class="label">Student Number:</span> {{ $student->student_number }}</p>
        @endif
        <p><span class="label">Admission Status:</span> Approved</p>
    </div>

    <p>
        Your full welcome pack has been sent by email and includes instructions for accessing The Hive,
        completing onboarding, and watching for orientation, registration, fees, uniform, and timetable updates.
    </p>

    <p>
        Please keep this letter for your records and present it when requested by the Admissions Office.
    </p>

    <div class="signature">
        <p>Sincerely,</p>
        <p><strong>The Admissions Team</strong><br>Honey Bee Culinary Institute</p>
    </div>
</body>
</html>
