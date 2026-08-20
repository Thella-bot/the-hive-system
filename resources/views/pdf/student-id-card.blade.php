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

        .card {
            width: 242pt;
            height: 153pt;
            position: relative;
            overflow: hidden;
        }

        /*
         * Background traced from the reference design: a solid yellow
         * field with one large rotated white ellipse and three black
         * shard accents. This MUST stay a pre-rendered raster image
         * (public/images/id-card-bg.png) rather than inline SVG or a
         * CSS transform+border-radius ellipse - both were tested against
         * a real dompdf 3.1 render and produced a blank/broken result.
         * If the design ever changes, regenerate the PNG from the SVG
         * source rather than hand-editing CSS here.
         */
        .bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 242pt;
            height: 153pt;
        }

        .logo {
            position: absolute;
            top: 9pt;
            left: 13pt;
            height: 18pt;
        }

        .title {
            position: absolute;
            top: 6pt;
            right: 13pt;
            background: #FFBD59;
            color: #ffffff;
            font-weight: 900;
            font-size: 11.5pt;
            letter-spacing: 0.4pt;
            padding: 4pt 13pt;
            border-radius: 9999pt;
        }

        .photo {
            position: absolute;
            top: 40pt;
            left: 13pt;
            width: 60pt;
            height: 88pt;
            object-fit: cover;
            border-radius: 3pt;
            border: 1.5pt solid #ffffff;
        }

        .initials-fallback {
            position: absolute;
            top: 40pt;
            left: 13pt;
            width: 60pt;
            height: 88pt;
            border-radius: 3pt;
            border: 1.5pt solid #ffffff;
            background: #fef3c7;
            color: #b45309;
            font-weight: 900;
            font-size: 18pt;
            text-align: center;
        }

        /*
         * Label/value pairs use absolute positioning rather than a
         * table. table-layout:fixed with per-cell CSS widths did not
         * hold the value column to its intended width in testing -
         * long values (a full name, a programme title) silently
         * overflowed past the card edge instead of staying within the
         * 101pt budget. Absolute positioning with a fixed left/width
         * rendered correctly and predictably in every test.
         */
        .label {
            position: absolute;
            left: 79pt;
            width: 47pt;
            font-weight: 700;
            font-size: 6pt;
            white-space: nowrap;
            color: #111827;
        }

        .value {
            position: absolute;
            left: 128pt;
            width: 101pt;
            font-weight: 900;
            font-size: 7pt;
            text-transform: uppercase;
            overflow: hidden;
            white-space: nowrap;
            color: #111827;
        }

        /*
         * Distinct condensed treatment for the course value, matching
         * the reference design's contrasting display font. Requires
         * public/fonts/Oswald-Bold.ttf to be registered with dompdf's
         * FontMetrics before render - see StudentIdController::download().
         * @font-face declared here alone is NOT enough; it was tested
         * and silently fell back to a default serif font.
         */
        .value-course {
            position: absolute;
            left: 128pt;
            width: 101pt;
            font-family: 'Oswald', 'DejaVu Sans', sans-serif;
            font-weight: 900;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: -0.1pt;
            overflow: hidden;
            white-space: nowrap;
            color: #111827;
        }

        .r1 {
            top: 44pt;
        }

        .r2 {
            top: 60pt;
        }

        .r3 {
            top: 76pt;
        }

        .r4 {
            top: 92pt;
        }

        .footer {
            position: absolute;
            bottom: 3pt;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 5pt;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="card">
        <img class="bg" src="{{ public_path('images/id-card-bg.png') }}" alt="">

        <img class="logo" src="{{ public_path('images/hbci-logo.png') }}" alt="Honey Bee Culinary Institute">
        <span class="title">STUDENT CARD</span>

        @if($photoPath)
            <img class="photo" src="{{ $photoPath }}" alt="Student photo">
        @else
            <div class="initials-fallback">{{ $initials }}</div>
        @endif

        <div class="label r1">STUDENT NO:</div>
        <div class="value r1">{{ $studentNumber ?? 'N/A' }}</div>

        <div class="label r2">NAME:</div>
        <div class="value r2">{{ $name }}</div>

        <div class="label r3">YEAR:</div>
        <div class="value r3">{{ $year }}</div>

        <div class="label r4">COURSE:</div>
        <div class="value-course r4">{{ $programme ?? 'N/A' }}</div>

        <div class="footer">
            This card is the property of Honey Bee Culinary Institute. Return to reception if found.
        </div>
    </div>
</body>

</html>
