<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Too many requests</title>
    <link href="/css/app.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-xl w-full text-center">
            <h1 class="text-2xl font-semibold mb-4">Too many requests</h1>
            <p class="text-gray-600 mb-6">You're doing that too often. Please wait a moment and try again.</p>
            <a href="{{ url()->previous() ?: url('/') }}" class="inline-block bg-hbci-gray text-white px-4 py-2 rounded hover:bg-gray-700">Go back</a>
        </div>
    </div>
</body>
</html>