@extends('pdf.layout')

@section('content')
<p style="font-weight:700; color:#8b0000;">STRICTLY PRIVATE AND CONFIDENTIAL</p>
<p><strong>{{ $student->full_name }}</strong><br>
    Student No: {{ $student->student_number }}<br>
    Programme: {{ $programme->name }}</p>

<p>Dear {{ $student->first_name }},</p>

<p class="subject">RE: NOTICE OF SUSPENSION --- {{ strtoupper($offence) }}</p>

<p>Further to the disciplinary proceedings conducted on <strong>{{ $hearing_date->format('d F Y') }}</strong>, and having considered all evidence and representations submitted, the Disciplinary Committee has resolved to suspend you from Honey Bee Culinary Institute with immediate effect.</p>

<table class="info-table">
    <tr>
        <td class="label">Suspension Type</td>
        <td class="value">{{ $suspension_type }}</td>
    </tr>
    <tr>
        <td class="label">Effective Date</td>
        <td class="value">{{ $effective_date->format('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">Duration</td>
        <td class="value">{{ $duration }}</td>
    </tr>
    <tr>
        <td class="label">Return Date</td>
        <td class="value">{{ $return_date ? $return_date->format('d F Y') : 'Until Further Notice' }}</td>
    </tr>
    <tr>
        <td class="label">Access to Campus</td>
        <td class="value">{{ $campus_access }}</td>
    </tr>
</table>

<p><strong>Conditions of Suspension</strong></p>
<p>During the period of suspension, the following conditions apply:</p>
<ol>
    <li>You are prohibited from entering the Institute premises without prior written authorisation from the Director.</li>
    <li>You are required to surrender your student identity card to the Student Affairs Office by <strong>{{ $surrender_date->format('d F Y') }}</strong>.</li>
    <li>You remain responsible for any outstanding academic assignments and their deadlines, unless otherwise communicated.</li>
    <li>You are not permitted to represent the Institute in any official capacity during the suspension period.</li>
</ol>
<p>You have the right to appeal this decision to the Appeals Committee within five (5) working days of receipt of this letter. An appeal must be submitted in writing, addressed to the Director.</p>
<p>This matter will be reviewed on <strong>{{ $review_date->format('d F Y') }}</strong>. The Institute reserves the right to extend or vary the terms of this suspension as circumstances warrant.</p>

<div class="signature-block">
    <p>Yours sincerely,</p>
    <div class="signature-line"></div>
    <p class="signature-name">{{ $director_name }}</p>
    <p class="signature-title">Director</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
    <p><em>cc: Head of Department, Student File</em></p>
</div>
@endsection