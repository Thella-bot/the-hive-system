@extends('pdf.layout')

@section('content')
<p><strong>{{ $recipient_title }} {{ $recipient_name }}</strong><br>
    {{ $recipient_position }}<br>
    {{ $recipient_org }}<br>
    {{ $recipient_city }}
</p>

<p>Dear {{ $recipient_title }} {{ $recipient_last_name }},</p>

<p class="subject">RE: ACADEMIC REFERENCE: {{ $student->full_name }}</p>

<p>It is with great pleasure that I write this reference in support of <strong>{{ $student->full_name }}</strong>, who is applying for {{ $application_for }}. I have known {{ $student->first_name }} in the capacity of <strong>{{ $relationship }}</strong> for a period of <strong>{{ $period_known }}</strong>.</p>

<p><strong>Academic Performance</strong></p>
<p>{{ $student->first_name }} enrolled in the {{ $programme->name }} programme at HBCI in {{ $start_year }} and {{ $completion_status }} with {{ $grade_summary }}. {{ $student->first_name }} maintained a consistent {{ $gpa_record }}, demonstrating a strong grasp of both theoretical foundations and practical application.</p>
<p>{{ $academic_achievements }}</p>

<p><strong>Personal Qualities & Character</strong></p>
<p>{{ $student->first_name }} is a {{ $character_traits }} individual who consistently {{ $character_examples }}. {{ $student->first_name }} approached challenges with professionalism and maturity.</p>
<p>{{ $character_details }}</p>

<p><strong>Industry Readiness</strong></p>
<p>{{ $industry_readiness }}</p>

<p>I recommend {{ $student->first_name }} without reservation and am confident that {{ $student->first_name }} will be an asset to any team or institution. Should you require any further information, please do not hesitate to contact me.</p>

<div class="signature-block">
    <p>Yours sincerely,</p>
    <div class="signature-line"></div>
    <p class="signature-name">{{ $referee_name }}</p>
    <p class="signature-title">{{ $referee_title }}</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
    <p>Direct Line: {{ $referee_phone }} | Email: {{ $referee_email }}</p>
</div>
@endsection