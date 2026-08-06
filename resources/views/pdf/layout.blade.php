<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>HBCI Document</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #1a1a1a;
            padding: 40px 45px;
            background: #fff;
        }

        .letterhead {
            text-align: center;
            border-bottom: 3px double #b8860b;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .letterhead img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 15px auto;
        }

        .letterhead .ref-date {
            font-size: 11px;
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            overflow: hidden;
        }

        .letterhead .ref-date span {
            display: inline-block;
        }

        .letterhead .ref-date .ref-label {
            float: left;
        }

        .letterhead .ref-date .date-label {
            float: right;
        }

        .content {
            min-height: 500px;
        }

        .content .subject {
            font-weight: 700;
            text-decoration: underline;
            margin: 15px 0 10px 0;
        }

        .content p {
            margin-bottom: 12px;
            text-align: justify;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 11px;
        }

        .info-table td,
        .info-table th {
            border: 1px solid #222;
            padding: 6px 10px;
            text-align: left;
            vertical-align: top;
        }

        .info-table th {
            background: #f2f2f2;
            font-weight: 700;
        }

        .info-table .label {
            width: 30%;
            background: #f9f9f9;
            font-weight: 600;
        }

        .info-table .value {
            width: 70%;
        }

        .finance-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 11px;
        }

        .finance-table th {
            background: #333;
            color: #fff;
            padding: 6px;
            text-align: center;
            border: 1px solid #333;
        }

        .finance-table td {
            padding: 6px;
            border: 1px solid #555;
        }

        .finance-table .totals td {
            font-weight: 700;
            border-top: 2px solid #000;
        }

        .signature-block {
            margin-top: 40px;
            padding-top: 20px;
        }

        .signature-line {
            margin-top: 50px;
            width: 250px;
            border-top: 1px solid #000;
        }

        .signature-name {
            font-weight: 700;
            margin-top: 4px;
        }

        .signature-title {
            font-style: italic;
        }

        .institute-footer {
            margin-top: 8px;
            font-size: 10px;
            color: #555;
        }

        .acknowledgement {
            margin-top: 30px;
            border-top: 1px dashed #777;
            padding-top: 20px;
        }

        .ack-line {
            display: inline-block;
            width: 200px;
            border-bottom: 1px solid #000;
            margin: 0 10px;
        }

        .clearfix {
            overflow: auto;
        }

        .float-right {
            float: right;
        }

        .text-center {
            text-align: center;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .mt-10 {
            margin-top: 10px;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .underline {
            text-decoration: underline;
        }

        .bold {
            font-weight: 700;
        }

        /* Certificate specific */
        .certificate-container {
            text-align: center;
            border: 8px double #b8860b;
            padding: 40px 20px;
            margin-top: 20px;
        }

        .certificate-title {
            font-size: 36px;
            font-weight: 700;
            color: #8b0000;
            letter-spacing: 4px;
        }

        .certificate-student {
            font-size: 28px;
            font-weight: 700;
            margin: 30px 0 10px 0;
            border-bottom: 2px solid #8b0000;
            display: inline-block;
            padding: 0 40px 10px 40px;
        }
    </style>
</head>

<body>
    <div class="letterhead">
        <img src="{{ public_path('images/hbci-letterhead-header.png') }}" alt="HBCI Letterhead">
        <div class="ref-date">
            <span class="ref-label"><strong>Ref:</strong> {{ $ref ?? 'HBCI/GEN/'.date('Y').'/001' }}</span>
            <span class="date-label"><strong>Date:</strong> {{ $date ?? now()->format('d F Y') }}</span>
        </div>
    </div>
    <div class="content">
        @yield('content')
    </div>
    <div style="margin-top: 40px; border-top: 1px solid #ccc; padding-top: 8px; text-align: center; font-size: 9px; color: #888;">
        Honey Bee Culinary Institute &bull; Maseru, Lesotho &bull; www.hbci.ac.ls
    </div>
</body>

</html>