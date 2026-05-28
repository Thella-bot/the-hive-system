<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title ?? 'Error' }}</title>
    <link href="/css/app.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-xl w-full text-center">
            <h1 class="text-2xl font-semibold mb-4">{{ $title ?? 'Something went wrong' }}</h1>
            <p class="text-gray-600 mb-6">{!! $message ?? 'An unexpected error occurred. Please try again later.' !!}</p>
            @isset($errorId)
            <p class="text-sm text-gray-400 mb-6">Reference: <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $errorId }}</code></p>
            @endisset
            <a href="{{ url()->previous() ?: url('/') }}" class="inline-block bg-hbci-gray text-white px-4 py-2 rounded hover:bg-gray-700">Go back</a>
        </div>
    </div>
</body>
</html>