@extends('pdf.layout')

@section('content')
<p><strong>{{ $student->title ?? '' }} {{ $student->full_name }}</strong><br>
    {{ $student->address ?? '' }}
</p>

<p>Dear {{ $student->first_name ?? 'Applicant' }},</p>

<p class="subject">RE: OUTCOME OF APPLICATION FOR ADMISSION --- {{ $programme->name }}</p>

<p>Thank you for your interest in Honey Bee Culinary Institute and for submitting an application for admission to the {{ $programme->name }} programme commencing {{ $intake_month }}.</p>

<p>The Admissions Committee has carefully reviewed all applications received for this intake. After thorough consideration of your academic record, portfolio, and supporting documents, we regret to inform you that we are unable to offer you a place in the programme at this time.</p>

<p>This decision is not a reflection of your potential. The volume of applications received and the limited number of available places make this a highly competitive process, and we encourage you to consider reapplying for a future intake.</p>

<p>Should you wish to receive feedback on your application or enquire about alternative programmes, please contact the Admissions Office at admissions@hbci.ac.ls or call {{ $phone ?? '+266 XXXX XXXX' }}.</p>

<p>We wish you every success in your future endeavours.</p>

<div class="signature-block">
    <p>Yours sincerely,</p>
    <div class="signature-line"></div>
    <p class="signature-name">{{ $registrar_name }}</p>
    <p class="signature-title">Registrar</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
</div>
@endsection