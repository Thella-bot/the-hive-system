@extends('pdf.layout')

@section('content')
<p><strong>Internal Memorandum</strong></p>

<table class="info-table" style="border: none;">
    <tr>
        <td style="border: none; width: 15%;"><strong>TO</strong></td>
        <td style="border: none;">{{ $to }}</td>
    </tr>
    <tr>
        <td style="border: none;"><strong>FROM</strong></td>
        <td style="border: none;">{{ $from_name }} --- {{ $from_designation }}</td>
    </tr>
    <tr>
        <td style="border: none;"><strong>DATE</strong></td>
        <td style="border: none;">{{ $date->format('d F Y') }}</td>
    </tr>
    <tr>
        <td style="border: none;"><strong>REF</strong></td>
        <td style="border: none;">{{ $ref }}</td>
    </tr>
    <tr>
        <td style="border: none;"><strong>SUBJECT</strong></td>
        <td style="border: none; font-weight:700;">{{ strtoupper($subject) }}</td>
    </tr>
    <tr>
        <td style="border: none;"><strong>COPIES</strong></td>
        <td style="border: none;">{{ $cc ?? 'N/A' }}</td>
    </tr>
</table>

<p><strong>1. Background / Purpose</strong></p>
<p>{{ $background }}</p>

<p><strong>2. Key Information / Instructions</strong></p>
<ul>
    @foreach($key_points as $point)
    <li>{{ $point }}</li>
    @endforeach
</ul>

<p><strong>3. Action Required</strong></p>
<p>{{ $action_required }}</p>

<p><strong>4. Enquiries</strong></p>
<p>Should you have any questions regarding the contents of this memo, please direct them to <strong>{{ $contact_name }}</strong> at {{ $contact_email }} or extension {{ $contact_ext }}.</p>

<div class="signature-block">
    <div class="signature-line"></div>
    <p class="signature-name">{{ $from_name }}</p>
    <p class="signature-title">{{ $from_designation }}</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
</div>
@endsection