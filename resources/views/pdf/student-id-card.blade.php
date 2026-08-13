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
            top: -50pt;
            right: -30pt;
            width: 140pt;
            height: 140pt;
            border-radius: 50%;
            background: #fbbf24;
        }

        .blob-bottom {
            position: absolute;
            bottom: -60pt;
            left: -40pt;
            width: 130pt;
            height: 130pt;
            border-radius: 50%;
            background: #fbbf24;
        }

        .corner-accent {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 48pt;
            height: 28pt;
            background: #000000;
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
        }

        .header {
            position: absolute;
            top: 12pt;
            left: 16pt;
            right: 16pt;
            height: 18pt;
        }

        .header img {
            height: 18pt;
        }

        .title {
            position: absolute;
            top: 34pt;
            left: 16pt;
            background: #f59e0b;
            color: #ffffff;
            font-weight: 900;
            font-size: 12pt;
            letter-spacing: 1.2pt;
            padding: 3pt 16pt;
            border-radius: 9999pt;
        }

        .content {
            position: absolute;
            top: 60pt;
            left: 16pt;
            right: 16pt;
            bottom: 14pt;
        }

        .photo {
            width: 48pt;
            height: 60pt;
            object-fit: cover;
            border-radius: 4pt;
            border: 2pt solid #ffffff;
            box-shadow: 0 1pt 2pt rgba(0, 0, 0, 0.15);
            background: #fffbeb;
        }

        .details {
            font-size: 8pt;
            line-height: 11pt;
            padding-top: 2pt;
        }

        .row {
            margin-bottom: 5pt;
        }

        .label {
            font-weight: 700;
            color: #111827;
        }

        .value {
            font-weight: 900;
            color: #111827;
            text-transform: uppercase;
            word-wrap: break-word;
        }

        .initials-fallback {
            width: 48pt;
            height: 60pt;
            border-radius: 4pt;
            border: 2pt solid #ffffff;
            box-shadow: 0 1pt 2pt rgba(0, 0, 0, 0.15);
            background: #fffbeb;
            color: #92400e;
            font-weight: 900;
            font-size: 14pt;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
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
                    <td width="48pt" valign="top" style="padding-right: 10pt;">
                        @if($photoPath)
                            <img class="photo" src="{{ $photoPath }}" alt="Student photo">
                        @else
                            <div class="initials-fallback">{{ $initials }}</div>
                        @endif
                    </td>
                    <td valign="top">
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
    </div>
</body>

</html>
