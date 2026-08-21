$ip = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.*" } | Select-Object -First 1).IPAddress
if (-not $ip) {
    $ip = "127.0.0.1"
    Write-Host "No network IP found, defaulting to localhost"
} else {
    Write-Host "Detected IP: $ip"
}

$envPath = ".env"
$content = Get-Content $envPath
$content = $content -replace 'APP_URL=http://\d+\.\d+\.\d+\.\d+:8000', "APP_URL=http://$ip`:8000"
$content = $content -replace 'VITE_DEV_SERVER_URL=http://\d+\.\d+\.\d+\.\d+:5173', "VITE_DEV_SERVER_URL=http://$ip`:5173"
Set-Content $envPath $content

Write-Host "Updated .env with current IP"
Write-Host "Starting Laravel server on http://$ip`:8000"
php artisan serve --host=0.0.0.0 --port=8000
