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
            background: #ffffff;
            border-radius: 10pt;
        }

        .banner {
            position: absolute;
            top: 0;
            left: 0;
            width: 242pt;
            height: 36pt;
            background: #FFBD59;
        }

        .banner-icon {
            position: absolute;
            top: 8pt;
            left: 12pt;
            height: 20pt;
        }

        .banner-text {
            position: absolute;
            top: 12pt;
            left: 38pt;
            color: #ffffff;
            font-weight: 900;
            font-size: 8.5pt;
            letter-spacing: 0.2pt;
            white-space: nowrap;
        }

        .photo {
            position: absolute;
            top: 42pt;
            right: 13pt;
            width: 46pt;
            height: 56pt;
            object-fit: cover;
            border-radius: 3pt;
            border: 1pt solid #e5e7eb;
        }

        .initials-fallback {
            position: absolute;
            top: 42pt;
            right: 13pt;
            width: 46pt;
            height: 56pt;
            border-radius: 3pt;
            border: 1pt solid #e5e7eb;
            background: #fef3c7;
            color: #b45309;
            font-weight: 900;
            font-size: 15pt;
            text-align: center;
            line-height: 56pt;
        }

        .name-caption {
            position: absolute;
            top: 99pt;
            right: 13pt;
            width: 66pt;
            text-align: center;
            color: #d9820c;
            font-weight: 900;
            font-size: 6pt;
            line-height: 7pt;
            max-height: 14pt;
            text-transform: uppercase;
            overflow: hidden;
            word-break: break-word;
        }

        .qr {
            position: absolute;
            top: 116pt;
            right: 20pt;
            width: 32pt;
            height: 32pt;
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
            left: 13pt;
            width: 58pt;
            font-weight: 700;
            font-size: 7pt;
            color: #374151;
        }

        .colon {
            position: absolute;
            left: 72pt;
            font-weight: 700;
            font-size: 7pt;
            color: #374151;
        }

        .value {
            position: absolute;
            left: 79pt;
            width: 88pt;
            font-weight: 700;
            font-size: 7pt;
            color: #111827;
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
            left: 79pt;
            width: 88pt;
            font-family: 'Oswald', 'DejaVu Sans', sans-serif;
            font-weight: 900;
            font-size: 7pt;
            text-transform: uppercase;
            overflow: hidden;
            white-space: nowrap;
            color: #111827;
        }

        .r1 {
            top: 48pt;
        }

        .r2 {
            top: 64pt;
        }

        .r3 {
            top: 80pt;
        }

        .r4 {
            top: 96pt;
        }

        .signature {
            position: absolute;
            bottom: 8pt;
            left: 13pt;
            font-size: 6pt;
            color: #9ca3af;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="banner">
            <img class="banner-icon" src="{{ public_path('images/hbci-bee-white.png') }}" alt="">
            <div class="banner-text">HONEY BEE CULINARY INSTITUTE</div>
        </div>

        @if($photoPath)
            <img class="photo" src="{{ $photoPath }}" alt="Student photo">
        @else
            <div class="initials-fallback">{{ $initials }}</div>
        @endif

        <div class="name-caption">{{ $name }}</div>

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
    </div>
</body>

</html>