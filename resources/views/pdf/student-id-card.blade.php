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
            top: -78pt;
            right: -30pt;
            width: 160pt;
            height: 160pt;
            background: #FBB040;
            border-radius: 50%;
        }
        .blob-bottom-left {
            position: absolute;
            bottom: -68pt;
            left: -50pt;
            width: 130pt;
            height: 130pt;
            background: #FBB040;
            border-radius: 50%;
        }
        .accent-black {
            position: absolute;
            bottom: -6pt;
            right: -14pt;
            width: 100pt;
            height: 46pt;
            background: #111111;
            transform: rotate(-10deg);
        }

        .content {
            position: relative;
            z-index: 2;
            padding: 9pt 13pt;
        }

        .top-row { width: 100%; border-collapse: collapse; }
        .top-row td { padding: 0; vertical-align: middle; }
        .logo { height: 20pt; }

        .title-badge {
            display: inline-block;
            background: #FBB040;
            color: #ffffff;
            font-size: 12pt;
            font-weight: bold;
            padding: 4pt 13pt;
            border-radius: 12pt;
        }

        .layout { width: 100%; border-collapse: collapse; margin-top: 7pt; }
        .layout td { padding: 0; vertical-align: middle; }

        .photo-cell { width: 62pt; padding-right: 10pt; }
        .photo-frame {
            width: 62pt;
            height: 84pt;
            overflow: hidden;
            border-radius: 3pt;
            background: #FBB040;
        }
        .photo-frame img { width: 62pt; }
        .photo-placeholder {
            width: 62pt;
            height: 84pt;
            line-height: 84pt;
            text-align: center;
            color: #ffffff;
            font-weight: bold;
            font-size: 20pt;
        }

        table.fields { width: 100%; border-collapse: collapse; }
        table.fields td { padding: 2.5pt 0; font-size: 8.5pt; vertical-align: top; }
        .field-label { color: #111111; font-weight: bold; width: 62pt; }
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
            <table class="top-row">
                <tr>
                    <td style="text-align: left;">
                        <img class="logo" src="{{ public_path('images/hbci-logo.png') }}">
                    </td>
                    <td style="text-align: right;">
                        <span class="title-badge">STUDENT CARD</span>
                    </td>
                </tr>
            </table>

            <table class="layout">
                <tr>
                    <td class="photo-cell">
                        <div class="photo-frame">
                            @if (!empty($card['photo_path']))
                                <img src="{{ $card['photo_path'] }}">
                            @else
                                <div class="photo-placeholder">{{ $card['initials'] ?? '?' }}</div>
                            @endif
                        </div>
                    </td>
                    <td>
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
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
