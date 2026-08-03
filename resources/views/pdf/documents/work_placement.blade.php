@extends('pdf.layout')

@section('content')
<p><strong>{{ $recipient_title }} {{ $recipient_name }}</strong><br>
    {{ $recipient_position }}<br>
    {{ $recipient_org }}<br>
    {{ $recipient_city }}
</p>

<p>Dear {{ $recipient_title }} {{ $recipient_last_name }},</p>

<p class="subject">RE: WORK PLACEMENT / INTERNSHIP REQUEST: {{ $student->full_name }}</p>

<p>On behalf of Honey Bee Culinary Institute, I write to formally request a work placement / internship opportunity for one of our students at your esteemed organisation. The details of the student are as follows:</p>

<table class="info-table">
    <tr>
        <td class="label">Student Name</td>
        <td class="value">{{ $student->full_name }}</td>
    </tr>
    <tr>
        <td class="label">Student Number</td>
        <td class="value">{{ $student->student_number }}</td>
    </tr>
    <tr>
        <td class="label">Programme</td>
        <td class="value">{{ $programme->name }}</td>
    </tr>
    <tr>
        <td class="label">Year of Study</td>
        <td class="value">Year {{ $year_of_study }} of {{ $total_years }}</td>
    </tr>
    <tr>
        <td class="label">Placement Period</td>
        <td class="value">{{ $placement_start->format('d F Y') }} to {{ $placement_end->format('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">Duration</td>
        <td class="value">{{ $duration }}</td>
    </tr>
    <tr>
        <td class="label">Proposed Start</td>
        <td class="value">{{ $proposed_start->format('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">Placement Type</td>
        <td class="value">{{ $placement_type }}</td>
    </tr>
</table>

<p><strong>Purpose of Placement</strong></p>
<p>This work placement forms a {{ strtolower($placement_type) }} component of the {{ $programme->name }} curriculum at HBCI. The primary objectives of the placement are to:</p>
<ol>
    <li>Provide the student with practical, industry-relevant experience in a professional {{ $industry_sector }} environment.</li>
    <li>Reinforce and apply theoretical knowledge acquired in the classroom setting.</li>
    <li>Develop professional competencies including teamwork, communication, time management, and customer service.</li>
    <li>Build the student's professional network and awareness of industry standards.</li>
</ol>

<p><strong>Institute Responsibilities</strong></p>
<p>The Institute undertakes to:</p>
<ul>
    <li>Ensure the student is adequately prepared and briefed before commencement of the placement.</li>
    <li>Provide the host organisation with a copy of the student's learning objectives and assessment criteria.</li>
    <li>Conduct a mid-placement and end-of-placement evaluation in liaison with the host supervisor.</li>
    <li>Maintain insurance coverage for the student during the placement period, where applicable.</li>
</ul>

<p><strong>Supervision & Assessment</strong></p>
<p>The student will be required to submit a work placement logbook and a final placement report. We kindly request that your organisation assign a qualified supervisor to oversee the student and complete the attached supervisor's evaluation form at the end of the placement.</p>
<p>We appreciate your continued support of HBCI students and look forward to a mutually beneficial partnership. Please do not hesitate to contact the Placement Coordinator at placement@hbci.ac.ls to confirm the arrangement.</p>

<div class="signature-block">
    <p>Yours sincerely,</p>
    <div class="signature-line"></div>
    <p class="signature-name">{{ $coordinator_name }}</p>
    <p class="signature-title">Work Placement Coordinator / Head of Department</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
    <p><em>Enc: Student Profile | Learning Objectives | Supervisor Evaluation Form</em></p>
</div>
@endsection