@extends('pdf.layout')

@section('content')
<p><strong>{{ $staff->profile->designation ?? '' }} {{ $staff->full_name }}</strong><br>
    {{ $staff->profile->address ?? '' }}
</p>

<p>Dear {{ $staff->first_name ?? 'Candidate' }},</p>

<p class="subject">RE: APPOINTMENT TO THE POSITION OF {{ $position }}</p>

<p>Following your successful interview and the favourable outcome of the selection process, Honey Bee Culinary Institute is pleased to offer you employment on the following terms and conditions:</p>

<table class="info-table">
    <tr>
        <td class="label">Position</td>
        <td class="value">{{ $position }}</td>
    </tr>
    <tr>
        <td class="label">Department</td>
        <td class="value">{{ $department }}</td>
    </tr>
    <tr>
        <td class="label">Employment Type</td>
        <td class="value">{{ $contract_type }}</td>
    </tr>
    <tr>
        <td class="label">Contract Period</td>
        <td class="value">{{ $contract_start->format('d/m/Y') }} -- {{ $contract_end ? $contract_end->format('d/m/Y') : 'Permanent' }}</td>
    </tr>
    <tr>
        <td class="label">Commencement Date</td>
        <td class="value">{{ $commencement_date->format('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">Reporting To</td>
        <td class="value">{{ $reporting_to }}</td>
    </tr>
    <tr>
        <td class="label">Gross Monthly Salary</td>
        <td class="value">M {{ number_format($salary, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Probationary Period</td>
        <td class="value">{{ $probation_period ?? 'Not Applicable' }}</td>
    </tr>
</table>

<p><strong>Terms and Conditions</strong></p>
<p>The appointment is subject to the terms and conditions set out in the Employment Contract attached hereto. Key provisions include:</p>
<ol>
    <li>Your duties and responsibilities are as described in the attached Job Description.</li>
    <li>Remuneration will be paid monthly in arrears on the {{ $pay_day ?? '25th' }} of each month.</li>
    <li>You are required to comply with the Institute's Code of Conduct and all applicable policies and procedures at all times.</li>
    <li>This appointment is subject to satisfactory completion of the probationary period, where applicable.</li>
    <li>Either party may terminate this agreement by providing {{ $notice_period ?? 'X weeks/months' }} written notice.</li>
</ol>
<p>Please sign and return the enclosed duplicate copy of this letter and the Employment Contract no later than <strong>{{ $acceptance_deadline->format('d F Y') }}</strong>. Failure to do so shall render this offer null and void.</p>
<p>We are delighted to welcome you to the HBCI team and look forward to your valued contribution.</p>

<div class="signature-block">
    <p>Yours sincerely,</p>
    <div class="signature-line"></div>
    <p class="signature-name">{{ $director_name }}</p>
    <p class="signature-title">Director</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
</div>

<div class="acknowledgement">
    <p>I, <span class="ack-line"></span> , accept this offer on the terms and conditions stated above.</p>
    <p>Signature: <span class="ack-line"></span> &nbsp;&nbsp; Date: <span class="ack-line"></span></p>
</div>
@endsection