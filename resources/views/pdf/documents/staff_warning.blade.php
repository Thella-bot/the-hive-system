@extends('pdf.layout')

@section('content')
<p style="font-weight:700; color:#8b0000;">STRICTLY PRIVATE AND CONFIDENTIAL</p>
<p><strong>{{ $staff->full_name }}</strong><br>
    Position: {{ $staff->position }}<br>
    Department: {{ $staff->department }}</p>

<p>Dear {{ $staff->first_name }},</p>

<p class="subject">RE: {{ strtoupper($warning_type) }} WRITTEN WARNING --- {{ strtoupper($offence) }}</p>

<p>This letter constitutes a formal {{ strtolower($warning_type) }} written warning issued to you following a disciplinary enquiry held on <strong>{{ $hearing_date->format('d F Y') }}</strong> in the presence of <strong>{{ $hr_rep }}</strong> in respect of the following:</p>
<p><em>{{ $incident_description }}</em></p>

<p>The above conduct constitutes a breach of <strong>{{ $policy_violated }}</strong> and is considered unacceptable by the Institute.</p>

<p>You are hereby formally warned that a recurrence of this or similar misconduct during the validity period of this warning (which expires on <strong>{{ $expiry_date->format('d F Y') }}</strong>) may result in more severe disciplinary action, up to and including termination of employment.</p>

<p><strong>Corrective Measures Required</strong></p>
<ol>
    @foreach($corrective_actions as $action)
    <li>{{ $action }}</li>
    @endforeach
</ol>

<p>You have the right to appeal this warning in writing to the Director within five (5) working days of receipt of this letter.</p>

<div class="signature-block">
    <p>Yours sincerely,</p>
    <div class="signature-line"></div>
    <p class="signature-name">{{ $hr_manager_name }}</p>
    <p class="signature-title">HR Manager</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
</div>

<div class="acknowledgement">
    <p>I confirm that I have received and read this letter and understand its contents and my right to appeal.</p>
    <p>Signature: <span class="ack-line"></span> &nbsp;&nbsp; Date: <span class="ack-line"></span></p>
</div>
@endsection