<!DOCTYPE html>
<html>
<head><title>Transcript</title><style>body{font-family: sans-serif;}.module{margin-bottom:15px;}</style></head>
<body>
<h1>Transcript</h1>
<p><strong>Student:</strong> {{ $student->name }}</p>
<p><strong>Date:</strong> {{ now()->toDateString() }}</p>
<h2>GPA: {{ $gpa }}%</h2>
@foreach($modules as $module)
<div class="module">
    <h3>{{ $module->name }} ({{ $module->code }})</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr><th>Assessment</th><th>Marks</th><th>Max</th><th>Weight</th></tr>
        @foreach($module->gradeItems as $item)
            @php $grade = $item->studentGrades->first(); @endphp
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $grade->marks ?? '-' }}</td>
                <td>{{ $item->max_marks }}</td>
                <td>{{ $item->weight * 100 }}%</td>
            </tr>
        @endforeach
    </table>
</div>
@endforeach
</body>
</html>