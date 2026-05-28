<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Data - HBCI</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f8f9fa; }
        .error-container { text-align: center; padding: 2rem; }
        h1 { color: #dc3545; font-size: 3rem; margin-bottom: 0.5rem; }
        p { color: #6c757d; font-size: 1.25rem; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>422</h1>
        <p>{{ $message ?? 'Invalid data provided.' }}</p>
    </div>
</body>
</html>
