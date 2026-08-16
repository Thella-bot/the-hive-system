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
            top: -40pt;
            right: -70pt;
            width: 230pt;
            height: 140pt;
            border-radius: 50%;
            background: #fbbf24;
        }

        .blob-bottom {
            position: absolute;
            bottom: -55pt;
            left: -90pt;
            width: 230pt;
            height: 140pt;
            border-radius: 50%;
            background: #fbbf24;
        }

        .corner-accent {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 70pt;
            height: 42pt;
            background: #000000;
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
        }

        .corner-accent-left {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 26pt;
            height: 13pt;
            background: #000000;
            clip-path: polygon(0 0, 100% 100%, 0 100%);
        }

        .header {
            position: absolute;
            top: 10pt;
            left: 14pt;
            right: 14pt;
            height: 26pt;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header img {
            height: 22pt;
        }

        .title {
            display: inline-block;
            background: #f59e0b;
            color: #ffffff;
            font-weight: 900;
            font-size: 13pt;
            letter-spacing: 0.6pt;
            padding: 5pt 16pt;
            border-radius: 9999pt;
        }

        .content {
            position: absolute;
            top: 44pt;
            left: 14pt;
            right: 14pt;
            bottom: 10pt;
        }

        .photo {
            width: 78pt;
            height: 108pt;
            object-fit: cover;
            border-radius: 4pt;
            border: 2pt solid #ffffff;
            box-shadow: 0 1pt 2pt rgba(0, 0, 0, 0.15);
            background: #fef3c7;
        }

        .initials-fallback {
            width: 78pt;
            height: 108pt;
            border-radius: 4pt;
            border: 2pt solid #ffffff;
            box-shadow: 0 1pt 2pt rgba(0, 0, 0, 0.15);
            background: #fef3c7;
            color: #b45309;
            font-weight: 900;
            font-size: 22pt;
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .details {
            font-size: 9pt;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
        }

        .details td {
            padding-bottom: 7pt;
            vertical-align: baseline;
        }

        .label {
            font-weight: 700;
            color: #111827;
            font-size: 8pt;
            white-space: nowrap;
            padding-right: 8pt;
        }

        .value {
            font-weight: 900;
            color: #111827;
            font-size: 10pt;
            text-transform: uppercase;
        }

        /* Distinct condensed treatment for the course value, to echo the
           reference design's contrasting display font. Swap in a real
           condensed webfont (e.g. Oswald) via @font-face for an exact match. */
        .value-course {
            font-weight: 900;
            color: #111827;
            font-size: 10.5pt;
            text-transform: uppercase;
            letter-spacing: -0.2pt;
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
        <div class="corner-accent-left"></div>

        <div class="header">
            <table>
                <tr>
                    <td align="left" valign="middle">
                        <img src="{{ public_path('images/hbci-logo.png') }}" alt="Honey Bee Culinary Institute">
                    </td>
                    <td align="right" valign="middle">
                        <span class="title">STUDENT CARD</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="content">
            <table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td width="78pt" valign="top">
                        @if($photoPath)
                            <img class="photo" src="{{ $photoPath }}" alt="Student photo">
                        @else
                            <div class="initials-fallback">{{ $initials }}</div>
                        @endif
                    </td>
                    <td valign="top" style="padding-left: 12pt;">
                        <div class="details">
                            <table>
                                <tr>
                                    <td class="label">STUDENT NO:</td>
                                    <td class="value">{{ $studentNumber ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">NAME:</td>
                                    <td class="value">{{ $name }}</td>
                                </tr>
                                <tr>
                                    <td class="label">YEAR:</td>
                                    <td class="value">{{ $year }}</td>
                                </tr>
                                <tr>
                                    <td class="label">COURSE:</td>
                                    <td class="value-course">{{ $programme ?? 'N/A' }}</td>
                                </tr>
                            </table>
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
