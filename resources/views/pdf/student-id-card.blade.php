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
            font-family: DejaVu Sans, sans-serif;
        }

        .card {
            width: 242pt;
            height: 153pt;
            background: #ffffff;
            border-radius: 12pt;
            overflow: hidden;
            position: relative;
        }

        .blob-top {
            position: absolute;
            top: -36pt;
            right: -20pt;
            width: 128pt;
            height: 128pt;
            border-radius: 50%;
            background: #fbbf24;
        }

        .blob-bottom {
            position: absolute;
            bottom: -48pt;
            left: -32pt;
            width: 112pt;
            height: 112pt;
            border-radius: 50%;
            background: #fbbf24;
        }

        .corner-accent {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 64pt;
            height: 38pt;
            background: #000000;
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
        }

        .header {
            position: absolute;
            top: 12pt;
            left: 16pt;
            right: 16pt;
            height: 24pt;
        }

        .header img {
            height: 24pt;
        }

        .title {
            position: absolute;
            top: 40pt;
            left: 16pt;
            background: #f59e0b;
            color: #ffffff;
            font-weight: 900;
            font-size: 15pt;
            letter-spacing: 0.8pt;
            padding: 5pt 18pt;
            border-radius: 9999pt;
        }

        .content {
            position: absolute;
            top: 74pt;
            left: 16pt;
            right: 16pt;
            bottom: 12pt;
        }

        .photo {
            width: 60pt;
            height: 72pt;
            object-fit: cover;
            border-radius: 4pt;
            border: 2pt solid #ffffff;
            box-shadow: 0 1pt 2pt rgba(0, 0, 0, 0.15);
            background: #fef3c7;
        }

        .initials-fallback {
            width: 60pt;
            height: 72pt;
            border-radius: 4pt;
            border: 2pt solid #ffffff;
            box-shadow: 0 1pt 2pt rgba(0, 0, 0, 0.15);
            background: #fef3c7;
            color: #b45309;
            font-weight: 900;
            font-size: 18pt;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .details {
            font-size: 9pt;
            line-height: 13pt;
        }

        .row {
            margin-bottom: 5pt;
        }

        .label {
            font-weight: 700;
            color: #111827;
            font-size: 7.5pt;
        }

        .value {
            font-weight: 900;
            color: #111827;
            font-size: 9pt;
            text-transform: uppercase;
            word-wrap: break-word;
        }

        .footer {
            position: absolute;
            bottom: 3pt;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 6pt;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="blob-top"></div>
        <div class="blob-bottom"></div>
        <div class="corner-accent"></div>

        <div class="header">
            <img src="{{ public_path('images/hbci-logo.png') }}" alt="Honey Bee Culinary Institute">
        </div>

        <div class="title">STUDENT CARD</div>

        <div class="content">
            <table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td width="60pt" valign="top">
                        @if($photoPath)
                            <img class="photo" src="{{ $photoPath }}" alt="Student photo">
                        @else
                            <div class="initials-fallback">{{ $initials }}</div>
                        @endif
                    </td>
                    <td valign="top" style="padding-left: 12pt;">
                        <div class="details">
                            <div class="row">
                                <span class="label">STUDENT NO:</span><br>
                                <span class="value">{{ $studentNumber ?? 'N/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="label">NAME:</span><br>
                                <span class="value">{{ $name }}</span>
                            </div>
                            <div class="row">
                                <span class="label">YEAR:</span><br>
                                <span class="value">{{ $year }}</span>
                            </div>
                            <div class="row">
                                <span class="label">COURSE:</span><br>
                                <span class="value">{{ $programme ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            This card is the property of Honey Bee Culinary Institute. Return to reception if found.
        </div>
    </div>
</body>

</html>
