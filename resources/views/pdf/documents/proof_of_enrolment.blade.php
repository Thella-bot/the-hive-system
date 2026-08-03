@extends('pdf.layout')

@section('content')
<p style="font-size:16px; font-weight:700; text-decoration:underline;">PROOF OF ENROLMENT</p>

<table class="info-table">
    <tr>
        <td class="label">Full Name</td>
        <td class="value">{{ $student->full_name }}</td>
    </tr>
    <tr>
        <td class="label">Student Number</td>
        <td class="value">{{ $student->student_number }}</td>
    </tr>
    <tr>
        <td class="label">Date of Birth</td>
        <td class="value">{{ $student->dob->format('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">National ID / Passport</td>
        <td class="value">{{ $student->id_number }}</td>
    </tr>
    <tr>
        <td class="label">Programme</td>
        <td class="value">{{ $programme->name }}</td>
    </tr>
    <tr>
        <td class="label">NQF Level</td>
        <td class="value">Level {{ $programme->nqf_level }}</td>
    </tr>
    <tr>
        <td class="label">Year of Study</td>
        <td class="value">Year {{ $year_of_study }} of {{ $total_years }}</td>
    </tr>
    <tr>
        <td class="label">Academic Year</td>
        <td class="value">{{ $academic_year }}</td>
    </tr>
    <tr>
        <td class="label">Mode of Study</td>
        <td class="value">{{ $mode_of_study }}</td>
    </tr>
    <tr>
        <td class="label">Enrolment Status</td>
        <td class="value">{{ strtoupper($status) }}</td>
    </tr>
    <tr>
        <td class="label">Enrolment Date</td>
        <td class="value">{{ $enrolment_date->format('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">Expected Completion</td>
        <td class="value">{{ $expected_completion->format('F Y') }}</td>
    </tr>
</table>

<p>This is to confirm that the above-named student is currently enrolled at Honey Bee Culinary Institute and is in good academic and financial standing as of the date of this letter.</p>
<p>This document is issued at the request of the student named above and is valid for a period of thirty (30) days from the date of issue.</p>
<p>This letter should not be altered or reproduced without the written authorisation of the Registrar. For verification, please contact the Registrar's Office at registrar@hbci.ac.ls.</p>

<div class="signature-block">
    <div class="signature-line"></div>
    <p class="signature-name">{{ $registrar_name }}</p>
    <p class="signature-title">Registrar</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
    <p><em>[OFFICIAL STAMP]</em></p>
</div>
@endsection