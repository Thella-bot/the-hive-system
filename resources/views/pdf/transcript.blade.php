@extends('pdf.layout')

@section('content')
<div style="border-left: 5px solid #f4b41a; padding: 8px 0 8px 14px; margin-bottom: 18px;">
    <div style="font-size: 22px; color: #13252b; font-weight: 700; line-height: 1.15;">Academic Transcript</div>
    <div style="font-size: 10px; color: #667085; margin-top: 3px;">Official record of academic performance</div>
</div>

<table class="info-table" style="border: 0; background: #f7f8fa;">
    <tr>
        <td class="label" style="border: 0; color: #667085;">Student Name</td>
        <td class="value" style="border: 0; font-weight: 700; color: #13252b;">{{ $student->name }}</td>
    </tr>
    <tr>
        <td class="label" style="border: 0; color: #667085;">Student Number</td>
        <td class="value" style="border: 0; color: #13252b;">{{ $student->student_number ?? $student->profile?->student_number ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="label" style="border: 0; color: #667085;">Programme</td>
        <td class="value" style="border: 0; color: #13252b;">{{ $student->programme?->name ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="label" style="border: 0; color: #667085;">GPA</td>
        <td class="value" style="border: 0; color: #a86f00; font-weight: 700;">{{ $gpa !== 'N/A' ? $gpa . '%' : 'N/A' }}</td>
    </tr>
</table>

@foreach($modulesByYear as $year => $yearEnrollments)
<p class="subject" style="margin-top: 18px; margin-bottom: 6px; color: #13252b; text-transform: uppercase; letter-spacing: 1px;">Academic Year {{ $year }}</p>

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
<div style="margin-top: 15px; text-align: left;">
    <strong>Overall GPA: {{ $gpa }}%</strong>
</div>
@endif

<p style="margin-top: 20px; font-size: 10px; color: #555;">
    This transcript is issued based on the academic records of Honey Bee Culinary Institute as of {{ now()->format('d F Y') }}.
</p>

<div class="signature-block" style="margin-top: 40px; padding-top: 20px; width: 250px; margin-right: auto; text-align: right;">
    <div class="signature-line" style="width: 100%;"></div>
    <p class="signature-name">Chef Evodia Mahali Monokoa</p>
    <p class="signature-title">Director</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
</div>
@endsection