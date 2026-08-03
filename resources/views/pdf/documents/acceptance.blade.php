@extends('pdf.layout')

@section('content')
<p><strong>{{ $student->title ?? '' }} {{ $student->full_name }}</strong><br>
    {{ $student->address ?? '' }}
</p>

<p>Dear {{ $student->first_name ?? 'Applicant' }},</p>

<p class="subject">OFFER OF ADMISSION</p>

<p>On behalf of the Management and Faculty of Honey Bee Culinary Institute, it is with great pleasure that we inform you that your application has been successful. You have been offered a place in the following programme:</p>

<table class="info-table">
    <tr>
        <td class="label">Programme</td>
        <td class="value">{{ $programme->name }}</td>
    </tr>
    <tr>
        <td class="label">NQF Level</td>
        <td class="value">Level {{ $programme->nqf_level ?? 'X' }}</td>
    </tr>
    <tr>
        <td class="label">Duration</td>
        <td class="value">{{ $programme->duration ?? 'X Years' }}</td>
    </tr>
    <tr>
        <td class="label">Intake Date</td>
        <td class="value">{{ $intake_date->format('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">Mode of Study</td>
        <td class="value">{{ $enrollment->mode ?? 'Full-Time' }}</td>
    </tr>
    <tr>
        <td class="label">Student No.</td>
        <td class="value">{{ $enrollment->student_number }}</td>
    </tr>
</table>

<p>This offer is conditional upon submission of the following documents to the Admissions Office no later than <strong>{{ $deadline->format('d F Y') }}</strong>:</p>
<ol>
    <li>Certified copy of National Identity Card or Passport</li>
    <li>Certified copies of all relevant academic certificates and transcripts</li>
    <li>Two (2) recent passport-size photographs</li>
    <li>Proof of payment of the registration fee of M {{ number_format($registration_fee, 2) }}</li>
    <li>Completed and signed Student Enrolment Form</li>
</ol>
<p>Failure to submit the required documents by the specified deadline may result in forfeiture of the offered place. The registration fee is non-refundable.</p>
<p>We look forward to welcoming you to the HBCI family. Should you have any enquiries, please contact the Admissions Office at admissions@hbci.ac.ls.</p>

<div class="signature-block">
    <p>Yours sincerely,</p>
    <div class="signature-line"></div>
    <p class="signature-name">{{ $registrar_name }}</p>
    <p class="signature-title">Registrar</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
</div>
@endsection