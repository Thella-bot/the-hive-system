@extends('pdf.layout')

@section('content')
<p style="font-size:16px; font-weight:700; text-decoration:underline;">ACADEMIC TRANSCRIPT</p>

<table class="info-table">
    <tr>
        <td class="label">Student Name</td>
        <td class="value">{{ $student->name }}</td>
    </tr>
    <tr>
        <td class="label">Student Number</td>
        <td class="value">{{ $student->student_number ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="label">Programme</td>
        <td class="value">{{ $student->programme?->name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="label">GPA</td>
        <td class="value">{{ $gpa !== 'N/A' ? $gpa . '%' : 'N/A' }}</td>
    </tr>
</table>

@foreach($modulesByYear as $year => $yearEnrollments)
<p class="subject" style="margin-top: 15px; margin-bottom: 5px;">Academic Year {{ $year }}</p>

<table class="finance-table">
    <thead>
        <tr>
            <th style="width: 12%; text-align: center;">Code</th>
            <th style="width: 50%; text-align: left;">Module</th>
            <th style="width: 10%; text-align: center;">Credits</th>
            <th style="width: 13%; text-align: center;">Assessments</th>
            <th style="width: 15%; text-align: center;">Grade</th>
        </tr>
    </thead>
    <tbody>
        @foreach($yearEnrollments as $enrollment)
        @php
            $module = $enrollment->module;
            $totalWeight = 0;
            $totalMarks = 0;
            foreach ($module->gradables as $gradable) {
                $submission = $gradable->submissions->first();
                if ($submission && $submission->grade !== null && $gradable->max_marks > 0) {
                    $totalMarks += ($submission->grade / $gradable->max_marks) * 100 * $gradable->weight;
                    $totalWeight += $gradable->weight;
                }
            }
            $averageGrade = $totalWeight > 0 ? round($totalMarks / $totalWeight, 1) : null;
        @endphp
        <tr>
            <td style="text-align: center;">{{ $module->code }}</td>
            <td>{{ $module->name }}</td>
            <td style="text-align: center;">{{ $module->credits }}</td>
            <td style="text-align: center;">{{ $module->gradables->count() }}</td>
            <td style="text-align: center; font-weight: 700;">{{ $averageGrade !== null ? $averageGrade . '%' : 'N/A' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endforeach

@if($gpa !== 'N/A')
<div style="margin-top: 15px; text-align: right;">
    <strong>Overall GPA: {{ $gpa }}%</strong>
</div>
@endif

<p style="margin-top: 20px; font-size: 10px; color: #555;">
    This transcript is issued based on the academic records of Honey Bee Culinary Institute as of {{ now()->format('d F Y') }}.
</p>

<div class="signature-block">
    <div class="signature-line"></div>
    <p class="signature-name">{{ $student->name }}</p>
    <p class="signature-title">Student</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
</div>
@endsection
