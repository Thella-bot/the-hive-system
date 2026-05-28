<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign in required</title>
    <link href="/css/app.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-xl w-full text-center">
            <h1 class="text-2xl font-semibold mb-4">Sign in required</h1>
            <p class="text-gray-600 mb-6">You need to be signed in to access this page. Please sign in and try again.</p>
            <a href="{{ route('login') }}" class="inline-block bg-hbci-gold text-white px-4 py-2 rounded hover:bg-hbci-gold-dark">Sign in</a>
        </div>
    </div>
</body>
</html>