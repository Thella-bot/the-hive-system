@extends('pdf.layout')

@section('content')
<p style="font-weight:700; color:#8b0000;">STRICTLY PRIVATE AND CONFIDENTIAL</p>
<p><strong>{{ $student->full_name }}</strong><br>
    Student No: {{ $student->student_number }}<br>
    Programme: {{ $programme->name }}</p>

<p>Dear {{ $student->first_name }},</p>

<p class="subject">RE: {{ strtoupper($warning_type) }} WRITTEN WARNING --- {{ strtoupper($offence) }}</p>

<p>This letter constitutes a formal {{ strtolower($warning_type) }} written warning issued to you following a disciplinary enquiry held on <strong>{{ $hearing_date->format('d F Y') }}</strong> in respect of the following matter:</p>
<p><em>{{ $incident_description }}</em></p>

<p>The above conduct is in contravention of the HBCI Student Code of Conduct, specifically <strong>{{ $rule_violated }}</strong>. The Institute considers this behaviour unacceptable.</p>

<p>You are hereby formally warned that any recurrence of this or similar misconduct will result in more severe disciplinary action, up to and including suspension or expulsion from the Institute.</p>

<p>You are required to meet with <strong>{{ $advisor_name }}</strong> by <strong>{{ $meeting_deadline->format('d F Y') }}</strong> to discuss the conditions attached to this warning and expectations going forward.</p>

<p>You have the right to appeal this warning in writing to the Director within five (5) working days of receipt of this letter.</p>

<div class="signature-block">
    <p>Yours sincerely,</p>
    <div class="signature-line"></div>
    <p class="signature-name">{{ $dean_name }}</p>
    <p class="signature-title">Student Affairs Officer</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
</div>

<div class="acknowledgement">
    <p>I confirm that I have received and understood the contents of this letter.</p>
    <p>Signature: <span class="ack-line"></span> &nbsp;&nbsp; Date: <span class="ack-line"></span></p>
</div>
@endsection