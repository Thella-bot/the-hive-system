<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Student ID Card</title>
    <style>
        @page {
            margin: 0;
            size: 242pt 153pt;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            background: #13252b;
        }

        /*
         * Redesigned per a reference layout the user supplied: a solid
         * colour header banner (institute identity), a plain white body
         * with a colon-aligned label/value list on the left, and a
         * photo + name + QR code stacked on the right. This replaces the
         * earlier yellow-field-with-white-ellipse background entirely -
         * that background image (id-card-bg.png) is no longer used here.
         */
        .card {
            width: 242pt;
            height: 153pt;
            position: relative;
            overflow: hidden;
            background: #13252b;
            border-radius: 8pt;
        }

        .accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 7pt;
            height: 153pt;
            background: #f4b41a;
        }

        .brand-mark {
            position: absolute;
            top: 8pt;
            left: 15pt;
            width: 19pt;
            height: 19pt;
            padding: 2pt;
            border-radius: 10pt;
            background: #f4b41a;
        }

        .brand-name {
            position: absolute;
            top: 9pt;
            left: 39pt;
            color: #f8f5ed;
            font-weight: 900;
            font-size: 7pt;
            letter-spacing: 0.45pt;
            white-space: nowrap;
        }

        .brand-subtitle {
            position: absolute;
            top: 19pt;
            left: 39pt;
            color: #d9e1dd;
            font-size: 4.5pt;
            letter-spacing: 0.8pt;
            text-transform: uppercase;
        }

        .card-title {
            position: absolute;
            top: 10pt;
            right: 14pt;
            color: #f4b41a;
            font-size: 5pt;
            font-weight: 900;
            letter-spacing: 1pt;
            text-transform: uppercase;
        }

        .status-badge {
            position: absolute;
            top: 25pt;
            right: 14pt;
            padding: 2pt 5pt;
            border-radius: 5pt;
            background: #d9f5e5;
            color: #17633c;
            font-size: 4pt;
            font-weight: 900;
            letter-spacing: 0.6pt;
            text-transform: uppercase;
        }

        .status-badge.is-expired {
            background: #fde4e4;
            color: #9b2c2c;
        }

        .info-panel {
            position: absolute;
            top: 36pt;
            left: 7pt;
            width: 157pt;
            height: 109pt;
            padding: 12pt 10pt;
            background: #f8f5ed;
        }

        .eyebrow {
            color: #a86f00;
            font-size: 4.5pt;
            font-weight: 900;
            letter-spacing: 1pt;
            text-transform: uppercase;
        }

        .record-code {
            position: absolute;
            top: 43pt;
            right: 92pt;
            color: #8b9692;
            font-size: 4pt;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
        }

        .data-rule {
            position: absolute;
            left: 17pt;
            width: 137pt;
            height: 0.5pt;
            background: #dfe5df;
        }

        .rule-1 {
            top: 66pt;
        }

        .rule-2 {
            top: 82pt;
        }

        .rule-3 {
            top: 98pt;
        }

        .validity {
            position: absolute;
            top: 133pt;
            left: 112pt;
            color: #a86f00;
            font-size: 4pt;
            font-weight: 900;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
        }

        .validity-value {
            position: absolute;
            top: 132pt;
            left: 137pt;
            color: #13252b;
            font-size: 5pt;
            font-weight: 900;
        }

        .photo {
            position: absolute;
            top: 44pt;
            right: 16pt;
            width: 48pt;
            height: 58pt;
            object-fit: cover;
            border-radius: 4pt;
            border: 2pt solid #f4b41a;
        }

        .initials-fallback {
            position: absolute;
            top: 44pt;
            right: 16pt;
            width: 48pt;
            height: 58pt;
            border-radius: 4pt;
            border: 2pt solid #f4b41a;
            background: #26434a;
            color: #f4b41a;
            font-weight: 900;
            font-size: 15pt;
            text-align: center;
            line-height: 55pt;
        }

        .name-caption {
            position: absolute;
            top: 105pt;
            right: 12pt;
            width: 56pt;
            text-align: center;
            color: #f8f5ed;
            font-weight: 900;
            font-size: 6pt;
            line-height: 7pt;
            max-height: 14pt;
            text-transform: uppercase;
            overflow: hidden;
            word-break: break-word;
        }

        .verify-label {
            position: absolute;
            top: 124pt;
            left: 177pt;
            color: #b6c7c3;
            font-size: 4pt;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
        }

        .secure-label {
            position: absolute;
            top: 132pt;
            left: 177pt;
            color: #f4b41a;
            font-size: 4pt;
            font-weight: 900;
            letter-spacing: 0.7pt;
            text-transform: uppercase;
        }

        .qr {
            position: absolute;
            top: 119pt;
            right: 16pt;
            width: 29pt;
            height: 29pt;
            padding: 2pt;
            background: #f8f5ed;
            border: 0.5pt solid #f4b41a;
            border-radius: 3pt;
        }

        /*
         * Label/value pairs use absolute positioning with a fixed-width
         * label column and a separately-positioned colon, rather than a
         * table - table-layout:fixed with per-cell CSS widths did not
         * hold column widths reliably in testing (see the git history on
         * this file). This keeps every row's colon aligned in a single
         * vertical column regardless of label length, matching the
         * reference design.
         */
        .label {
            position: absolute;
            left: 17pt;
            width: 45pt;
            font-weight: 700;
            font-size: 5.5pt;
            color: #65716e;
        }

        .colon {
            position: absolute;
            left: 63pt;
            font-weight: 700;
            font-size: 5.5pt;
            color: #a86f00;
        }

        .value {
            position: absolute;
            left: 70pt;
            width: 83pt;
            font-weight: 700;
            font-size: 5.5pt;
            color: #13252b;
            overflow: hidden;
            white-space: nowrap;
        }

        /*
         * Distinct condensed treatment for the programme value, matching
         * the reference design's contrasting display font. Requires
         * public/fonts/Oswald-Bold.ttf to be registered with dompdf's
         * FontMetrics before render - see StudentIdController::download().
         * @font-face declared here alone is NOT enough; it was tested
         * and silently fell back to a default serif font.
         */
        .value-programme {
            position: absolute;
            left: 70pt;
            width: 83pt;
            font-family: 'Oswald', 'DejaVu Sans', sans-serif;
            font-weight: 900;
            font-size: 5.5pt;
            text-transform: uppercase;
            overflow: hidden;
            white-space: nowrap;
            color: #13252b;
        }

        .r1 {
            top: 54pt;
        }

        .r2 {
            top: 70pt;
        }

        .r3 {
            top: 86pt;
        }

        .r4 {
            top: 102pt;
        }

        .signature {
            position: absolute;
            bottom: 8pt;
            left: 17pt;
            font-size: 4.5pt;
            color: #8b9692;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="header">
            <img class="brand-mark" src="{{ public_path('images/hbci-logo-no-text.png') }}" alt="">
            <div class="brand-name">HONEY BEE CULINARY INSTITUTE</div>
            <div class="brand-subtitle">Learn. Create. Lead.</div>
            <div class="card-title">Student identity</div>
            <div class="status-badge {{ ($status ?? 'Active') === 'Expired' ? 'is-expired' : '' }}">{{ $status ?? 'Active' }}</div>
        </div>

        <div class="accent"></div>

        <div class="info-panel">
            <div class="eyebrow">Official student record</div>
        </div>

        <div class="record-code">HBCI / ID</div>
        <div class="data-rule rule-1"></div>
        <div class="data-rule rule-2"></div>
        <div class="data-rule rule-3"></div>

        @if($photoPath)
        <img class="photo" src="{{ $photoPath }}" alt="Student photo">
        @else
        <div class="initials-fallback">{{ $initials }}</div>
        @endif

        <div class="name-caption">{{ $name }}</div>

        <div class="verify-label">Scan to verify</div>
        <div class="secure-label">Secure ID check</div>

        @if($qrCode)
        <img class="qr" src="{{ $qrCode }}" alt="Scan to verify">
        @endif

        <div class="label r1">Student ID</div>
        <div class="colon r1">:</div>
        <div class="value r1">{{ $studentNumber ?? 'N/A' }}</div>

        <div class="label r2">Programme</div>
        <div class="colon r2">:</div>
        <div class="value-programme r2">{{ $programme ?? 'N/A' }}</div>

        <div class="label r3">Year</div>
        <div class="colon r3">:</div>
        <div class="value r3">{{ $year }}</div>

        <div class="label r4">Cohort</div>
        <div class="colon r4">:</div>
        <div class="value r4">{{ $cohort ?? 'N/A' }}</div>

        <div class="signature">Authorize Signature</div>
        <div class="validity">Valid thru</div>
        <div class="validity-value">{{ $validUntil ?? 'Active' }}</div>
    </div>
</body>

</html>