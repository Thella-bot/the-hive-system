<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Student ID Card</title>
    <style>
        @page {
            margin: 0;
            size: 242.65pt 153pt;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            background: #fff;
        }

        .card {
            width: 242.65pt;
            height: 153pt;
            position: relative;
            overflow: hidden;
            background: #fff;
            border-radius: 0;
        }

        .reference-background {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
            width: 242.65pt;
            height: 153pt;
        }

        .reference-content {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .reference-photo-frame {
            border: 2pt solid #fff;
            border-radius: 7pt 7pt 5pt 5pt;
            box-shadow: 0 1.5pt 4pt rgba(17, 24, 39, 0.2);
        }

        .reference-logo {
            position: absolute;
            top: 19pt;
            left: 16pt;
            width: 21pt;
            height: 21pt;
            object-fit: contain;
        }

        .reference-brand {
            position: absolute;
            top: 20pt;
            left: 39pt;
            width: 58pt;
            color: #ffbf5b;
            font-size: 5.5pt;
            line-height: 1.05;
            font-weight: 900;
            text-transform: uppercase;
        }

        .reference-title {
            position: absolute;
            top: 23.5pt;
            left: 101pt;
            width: 106pt;
            padding: 3.5pt 0;
            border-radius: 16pt;
            background: #ffbf5b;
            color: #fff;
            font-size: 11.5pt;
            line-height: 1;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            box-shadow: 0 0.5pt 0 rgba(0, 0, 0, 0.04);
        }

        .reference-photo,
        .reference-initials {
            position: absolute;
            top: 42pt;
            left: 13pt;
            width: 74pt;
            height: 95pt;
            object-fit: cover;
        }

        .reference-initials {
            background: #e8edf0;
            color: #13252b;
            font-size: 19pt;
            font-weight: 900;
            text-align: center;
            line-height: 95pt;
        }

        .reference-label,
        .reference-value {
            position: absolute;
            top: 53pt;
            color: #111111;
            font-family: 'Helvetica', 'Arial', sans-serif;
            height: 7pt;
            font-size: 6pt;
            font-weight: 700;
            line-height: 7pt;
            letter-spacing: 0;
            vertical-align: top;
        }

        .reference-label {
            left: 105pt;
        }

        .reference-value {
            left: 152pt;
            width: 73pt;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: uppercase;
            font-weight: 700;
        }

        .reference-name-label {
            top: 70.5pt;
        }

        .reference-name-value {
            top: 70.5pt;
            left: 132pt;
            width: 93pt;
            text-transform: uppercase;
        }

        .reference-year-label {
            top: 88pt;
        }

        .reference-year-value {
            top: 88pt;
            left: 132pt;
            width: 93pt;
            text-transform: uppercase;
        }

        .reference-course-label {
            top: 105.5pt;
            height: 7pt;
            line-height: 7pt;
        }

        .reference-course-value {
            top: 105.5pt;
            left: 136pt;
            width: 89pt;
            font-family: 'Oswald', 'DejaVu Sans', sans-serif;
            font-size: 5.5pt;
            font-weight: 700;
            height: 7pt;
            line-height: 7pt;
            white-space: nowrap;
            overflow: hidden;
            text-transform: uppercase;
            text-overflow: ellipsis;
        }
    </style>
</head>

<body>
    <div class="card">
        <img class="reference-background" src="{{ public_path('images/id-card-bg.png') }}" alt="">
        <div class="reference-content">
            <img class="reference-logo" src="{{ public_path('images/hbci-logo-no-text.png') }}" alt="">
            <div class="reference-brand">Honey Bee<br>Culinary Institute</div>
            <div class="reference-title">Student Card</div>
            @if($photoPath)
            <img class="reference-photo reference-photo-frame" src="{{ $photoPath }}" alt="Student photo">
            @else
            <div class="reference-initials reference-photo-frame">{{ $initials }}</div>
            @endif
            <div class="reference-label">STUDENT NO:</div>
            <div class="reference-value">{{ $studentNumber ?? 'N/A' }}</div>
            <div class="reference-label reference-name-label">NAME:</div>
            <div class="reference-value reference-name-value">{{ strtoupper($name) }}</div>
            <div class="reference-label reference-year-label">YEAR:</div>
            <div class="reference-value reference-year-value">{{ $year }}</div>
            <div class="reference-label reference-course-label">COURSE:</div>
            <div class="reference-value reference-course-value">{{ $programme ?? 'N/A' }}</div>
        </div>
    </div>
</body>

</html>