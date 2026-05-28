<!DOCTYPE html>
<html>
<head>
    <title>Official Transcript</title>
    <style>
        @page {
            margin: 0.5in;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }
        .header img {
            width: 80px;
            height: auto;
            position: absolute;
            top: 0;
            left: 0;
        }
        .header .institute-details {
            display: inline-block;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22pt;
            font-weight: bold;
            color: #003366;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14pt;
            font-weight: normal;
            color: #444;
        }
        .student-info {
            margin-top: 30px;
            margin-bottom: 30px;
            border: 1px solid #ccc;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .student-info td {
            padding: 4px 0;
            border: none;
        }
        .student-info .label {
            font-weight: bold;
            width: 150px;
        }
        .module-section {
            margin-top: 25px;
        }
        .module-header {
            font-size: 12pt;
            font-weight: bold;
            color: #003366;
            border-bottom: 2px solid #003366;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .grades-table th, .grades-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .grades-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .grades-table .no-grades {
            text-align: center;
            font-style: italic;
            color: #777;
        }
        .summary-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #003366;
            text-align: right;
        }
        .summary-footer .gpa {
            font-size: 14pt;
            font-weight: bold;
            color: #003366;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/hbci-logo.png');
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;
    @endphp

    <div class="header">
        <img src="{{ $logoSrc }}" alt="HBCI Logo">
        <div class="institute-details">
            <h1>Honey Bee Culinary Institute</h1>
            <h2>Official Academic Transcript</h2>
        </div>
    </div>

    <div class="student-info">
        <table>
            <tr>
                <td class="label">Student Name:</td>
                <td>{{ $student->name }}</td>
            </tr>
            <tr>
                <td class="label">Student ID:</td>
                <td>{{ $student->id }}</td>
            </tr>
            <tr>
                <td class="label">Date Issued:</td>
                <td>{{ date('F j, Y') }}</td>
            </tr>
        </table>
    </div>

    @foreach ($modules as $module)
        <div class="module-section">
            <div class="module-header">
                {{ $module->name }} ({{ $module->code }}) - Credits: {{ $module->credits }}
            </div>
            <table class="grades-table">
                <thead>
                    <tr>
                        <th>Assessment</th>
                        <th>Marks</th>
                        <th>Weight</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($module->gradeItems as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>
                                @php
                                    $studentGrade = $item->studentGrades->first();
                                @endphp
                                {{ $studentGrade ? $studentGrade->marks . ' / ' . $item->max_marks : 'N/A' }}
                            </td>
                            <td>{{ $item->weight * 100 }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="no-grades">No grade items recorded for this module.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="summary-footer">
        <div class="gpa">
            Cumulative GPA: {{ $gpa }}
        </div>
    </div>

</body>
</html>