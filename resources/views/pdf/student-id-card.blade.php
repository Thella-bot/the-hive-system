<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student ID Card</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            width: 242pt;
            height: 153pt;
        }
        .card {
            position: relative;
            width: 242pt;
            height: 153pt;
            background: #ffffff;
            overflow: hidden;
        }

        /* Decorative brand shapes, echoing the HBCI orange/black identity */
        .blob-top-right {
            position: absolute;
            top: -60pt;
            right: -40pt;
            width: 140pt;
            height: 140pt;
            background: #FBB040;
            border-radius: 50%;
        }
        .blob-bottom-left {
            position: absolute;
            bottom: -55pt;
            left: -45pt;
            width: 120pt;
            height: 120pt;
            background: #FBB040;
            border-radius: 50%;
        }
        .accent-black {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 90pt;
            height: 40pt;
            background: #111111;
            transform: rotate(-8deg) translate(10pt, 12pt);
        }

        .content {
            position: relative;
            z-index: 2;
            padding: 10pt 14pt;
        }

        .brand-row {
            display: block;
            width: 100%;
        }
        .logo {
            height: 22pt;
        }

        .title-badge {
            display: inline-block;
            background: #FBB040;
            color: #ffffff;
            font-size: 15pt;
            font-weight: bold;
            padding: 4pt 16pt;
            border-radius: 14pt;
            margin: 8pt 0 10pt 0;
        }

        table.fields { width: 100%; border-collapse: collapse; margin-top: 2pt; }
        table.fields td { padding: 3pt 0; font-size: 9pt; vertical-align: top; }
        .field-label { color: #111111; font-weight: bold; width: 90pt; }
        .field-value { color: #111111; font-weight: bold; }
        .course-value { text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="card">
        <div class="blob-top-right"></div>
        <div class="blob-bottom-left"></div>
        <div class="accent-black"></div>

        <div class="content">
            <img class="logo" src="{{ public_path('images/hbci-logo.png') }}">

            <div class="title-badge">STUDENT CARD</div>

            <table class="fields">
                <tr>
                    <td class="field-label">STUDENT NO:</td>
                    <td class="field-value">{{ $card['student_number'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="field-label">NAME:</td>
                    <td class="field-value">{{ strtoupper($card['name']) }}</td>
                </tr>
                <tr>
                    <td class="field-label">YEAR:</td>
                    <td class="field-value">{{ $card['year'] }}</td>
                </tr>
                <tr>
                    <td class="field-label">COURSE:</td>
                    <td class="field-value course-value">{{ $card['programme'] ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
