<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student ID Card</title>
    <style>
        @page { margin: 0; size: 242pt 153pt; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; background: #eef0f3; }

        .card {
            width: 242pt;
            height: 153pt;
            position: relative;
            overflow: hidden;
            background: #ffffff;
            border-radius: 14pt;
        }

        .header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 39pt;
            background: #f7931e;
            border-radius: 14pt 14pt 0 0;
        }

        /* Keep the existing institute logo asset, but place it in the new header. */
        .logo {
            position: absolute;
            top: 9pt;
            left: 13pt;
            height: 21pt;
            width: auto;
        }

        .institute-name {
            position: absolute;
            top: 10pt;
            left: 48pt;
            right: 10pt;
            color: #ffffff;
            font-size: 12pt;
            line-height: 17pt;
            font-weight: 900;
            letter-spacing: 0.25pt;
            white-space: nowrap;
            overflow: hidden;
        }

        .details {
            position: absolute;
            left: 14pt;
            top: 51pt;
            width: 108pt;
        }

        .row {
            height: 15pt;
            line-height: 15pt;
            white-space: nowrap;
            overflow: hidden;
        }

        .label {
            display: inline-block;
            width: 43pt;
            color: #111827;
            font-size: 6.5pt;
            font-weight: 700;
            vertical-align: top;
        }

        .value {
            display: inline-block;
            width: 62pt;
            color: #111827;
            font-size: 7pt;
            font-weight: 900;
            text-transform: uppercase;
            vertical-align: top;
            overflow: hidden;
            white-space: nowrap;
        }

        .programme { font-size: 6.6pt; letter-spacing: -0.1pt; }

        .photo,
        .initials-fallback {
            position: absolute;
            top: 49pt;
            right: 20pt;
            width: 48pt;
            height: 55pt;
            border-radius: 2pt;
        }

        .photo {
            object-fit: cover;
            border: 0.7pt solid #d1d5db;
        }

        .initials-fallback {
            background: #e5e7eb;
            color: #9a3412;
            font-size: 15pt;
            font-weight: 900;
            text-align: center;
            line-height: 55pt;
            border: 0.7pt solid #d1d5db;
        }

        .name {
            position: absolute;
            top: 106pt;
            left: 151pt;
            width: 72pt;
            color: #f7931e;
            font-size: 9.5pt;
            line-height: 11pt;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
        }

        .qr-code {
            position: absolute;
            right: 20pt;
            bottom: 7pt;
            width: 39pt;
            height: 39pt;
            padding: 1.5pt;
            background: #ffffff;
            border: 0.5pt solid #e5e7eb;
            border-radius: 2pt;
        }

        .signature {
            position: absolute;
            left: 15pt;
            bottom: 9pt;
            color: #111827;
            font-size: 6.5pt;
            white-space: nowrap;
        }

        .footer {
            position: absolute;
            left: 15pt;
            bottom: 2.5pt;
            width: 125pt;
            color: #9ca3af;
            font-size: 3.6pt;
            line-height: 4.5pt;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header"></div>
        <img class="logo" src="{{ public_path('images/hbci-logo.png') }}" alt="Honey Bee Culinary Institute">
        <div class="institute-name">HONEY BEE CULINARY INSTITUTE</div>

        <div class="details">
            <div class="row">
                <span class="label">STUDENT ID:</span>
                <span class="value">{{ $studentNumber ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">PROGRAMME:</span>
                <span class="value programme">{{ $programme ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">YEAR:</span>
                <span class="value">{{ $year ?? 'N/A' }}</span>
            </div>
            <div class="row">
                <span class="label">COHORT:</span>
                <span class="value">{{ $cohort ?? 'N/A' }}</span>
            </div>
        </div>

        @if($photoPath)
            <img class="photo" src="{{ $photoPath }}" alt="Student photo">
        @else
            <div class="initials-fallback">{{ $initials }}</div>
        @endif

        <div class="name">{{ $name }}</div>

        @if($qrCode)
            <img class="qr-code" src="{{ $qrCode }}" alt="QR Code">
        @endif

        <div class="signature">Authorize Signature</div>
        <div class="footer">This card is the property of Honey Bee Culinary Institute. Return to reception if found.</div>
    </div>
</body>
</html>
