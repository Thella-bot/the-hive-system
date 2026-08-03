@extends('pdf.layout')

@section('content')
<p style="font-weight:700; color:#8b0000;">STRICTLY PRIVATE AND CONFIDENTIAL</p>
<p><strong>{{ $student->full_name }}</strong><br>
    Student No: {{ $student->student_number }}<br>
    Programme: {{ $programme->name }}</p>

<p>Dear {{ $student->first_name }},</p>

<p class="subject">RE: NOTICE OF EXPULSION</p>

<p>This letter serves as formal notice that, following a full disciplinary hearing held on <strong>{{ $hearing_date->format('d F Y') }}</strong> at which you were given the opportunity to be heard and to present your case, the Disciplinary Committee has resolved to expel you from Honey Bee Culinary Institute with immediate effect as of <strong>{{ $effective_date->format('d F Y') }}</strong>.</p>

<p><strong>Grounds for Expulsion</strong></p>
<p>This decision was reached on the basis of the following findings:</p>
<ol>
    @foreach($grounds as $ground)
    <li>{{ $ground }}</li>
    @endforeach
</ol>

<p><strong>Administrative Requirements</strong></p>
<p>You are required to comply with the following no later than <strong>{{ $compliance_deadline->format('d F Y') }}</strong>:</p>
<ol>
    <li>Return your student identity card and all Institute property to the Student Affairs Office.</li>
    <li>Settle any outstanding fees or financial obligations to the Finance Office.</li>
    <li>Collect any personal belongings from Institute premises in the presence of a staff member.</li>
</ol>
<p>Any academic transcripts or records may be requested through the Registrar's Office upon settlement of all outstanding obligations.</p>
<p>You have the right to appeal this decision to the Board of Governors within ten (10) working days of receipt of this letter. Appeals must be submitted in writing to the Registrar.</p>

<div class="signature-block">
    <p>Yours sincerely,</p>
    <div class="signature-line"></div>
    <p class="signature-name">{{ $director_name }}</p>
    <p class="signature-title">Director</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
    <p><em>cc: Board of Governors, Head of Department, Student File</em></p>
</div>
@endsection