@extends('pdf.layout')

@section('content')
<div class="certificate-container">
    <p style="font-size:14px; font-weight:700; letter-spacing:2px;">HONEY BEE CULINARY INSTITUTE</p>
    <p style="font-size:12px; font-style:italic; margin-bottom:20px;">Excellence in Culinary Arts & Hospitality</p>

    <p class="certificate-title">CERTIFICATE OF COMPLETION</p>
    <p style="font-size:14px; margin-top:20px;">This is to certify that</p>

    <p class="certificate-student">{{ strtoupper($student->full_name) }}</p>

    <p style="font-size:14px;">has successfully completed the</p>
    <p style="font-size:20px; font-weight:700; margin-top:5px;">{{ $programme->name }}</p>
    <p style="font-size:12px; margin-top:5px;">at NQF Level {{ $programme->nqf_level }} | Duration: {{ $programme->duration }}</p>

    @if(isset($award))
    <p style="font-size:14px; font-weight:600; margin-top:10px;">Awarded with {{ $award }}</p>
    @endif

    <table style="width:100%; margin-top:30px; border: none;">
        <tr>
            <td style="border: none; text-align:center; width:33%;">
                <div style="border-top:1px solid #000; width:80%; display:inline-block;"></div>
                <p style="margin-top:5px;"><strong>{{ $director_name }}</strong></p>
                <p style="font-size:10px;">Director</p>
            </td>
            <td style="border: none; text-align:center; width:33%;">
                <div style="border-top:1px solid #000; width:80%; display:inline-block;"></div>
                <p style="margin-top:5px;"><strong>{{ $issue_date->format('d F Y') }}</strong></p>
                <p style="font-size:10px;">Date of Issue</p>
            </td>
            <td style="border: none; text-align:center; width:33%;">
                <div style="border-top:1px solid #000; width:80%; display:inline-block;"></div>
                <p style="margin-top:5px;"><strong>{{ $registrar_name }}</strong></p>
                <p style="font-size:10px;">Registrar</p>
            </td>
        </tr>
    </table>

    <p style="font-size:10px; margin-top:20px;">Certificate No: {{ $certificate_number }} | Maseru, Lesotho | www.hbci.ac.ls</p>
</div>
@endsection