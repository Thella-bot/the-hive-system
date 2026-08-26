# start-server.ps1 - Start Laravel development server
param(
    [int]$Port = 8000,
    [switch]$OpenBrowser
)

$ErrorActionPreference = "Stop"

# --- Helper Functions ---
function Write-Status($msg) { Write-Host "[INFO] $msg" -ForegroundColor Cyan }
function Write-Success($msg) { Write-Host "[OK]   $msg" -ForegroundColor Green }
function Write-Warn($msg) { Write-Host "[WARN] $msg" -ForegroundColor Yellow }
function Write-Fail($msg) { Write-Host "[FAIL] $msg" -ForegroundColor Red }

# --- Validate Prerequisites ---
if (-not (Test-Path "artisan")) {
    Write-Fail "artisan not found. Run this script from the Laravel project root."
    exit 1
}

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Fail "PHP not found in PATH. Install PHP and ensure it's accessible."
    exit 1
}

# --- Detect Best IP ---
# Priority: WiFi > Ethernet > Other > Localhost
$preferredInterfaces = @("WiFi", "Ethernet", "WLAN")
$networkIPs = Get-NetIPAddress -AddressFamily IPv4 |
    Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.*" }

$ip = $null
foreach ($pref in $preferredInterfaces) {
    $match = $networkIPs | Where-Object { $_.InterfaceAlias -like "*$pref*" } | Select-Object -First 1
    if ($match) {
        $ip = $match.IPAddress
        break
    }
}

# Fallback to first available IP
if (-not $ip -and $networkIPs) {
    $ip = $networkIPs | Select-Object -First 1 | ForEach-Object { $_.IPAddress }
}

# Final fallback
if (-not $ip) {
    $ip = "127.0.0.1"
    Write-Warn "No network IP found, using localhost"
}

Write-Success "Detected IP: $ip"

# --- Check if Port is Already in Use ---
$existing = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue | Where-Object { $_.State -eq "Listen" }
if ($existing) {
    Write-Warn "Port $Port is already in use."
    $response = Read-Host "Kill existing process and restart? (y/n)"
    if ($response -eq 'y') {
        $existing | ForEach-Object {
            Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue
        }
        Start-Sleep -Seconds 1
    } else {
        Write-Fail "Cannot start server. Port $Port occupied."
        exit 1
    }
}

# --- Update .env ---
$envPath = ".env"
if (Test-Path $envPath) {
    Write-Status "Updating .env with current IP..."

    $content = Get-Content $envPath -Raw

    # Update APP_URL
    $content = $content -replace 'APP_URL=http://[^\s:]+:\d+', "APP_URL=http://${ip}:${Port}"

    # Update Vite dev URL (keep port 5173 or custom)
    $vitePort = 5173
    $content = $content -replace 'VITE_DEV_SERVER_URL=http://[^\s:]+:\d+', "VITE_DEV_SERVER_URL=http://${ip}:${vitePort}"

    # Update Sanctum stateful domains
    if ($content -match 'SANCTUM_STATEFUL_DOMAINS=') {
        $content = $content -replace 'SANCTUM_STATEFUL_DOMAINS=[^\s]+', "SANCTUM_STATEFUL_DOMAINS=${ip}:${Port}"
    }

    # Update session domain
    if ($content -match 'SESSION_DOMAIN=') {
        $content = $content -replace 'SESSION_DOMAIN=[^\s]+', "SESSION_DOMAIN=${ip}"
    }

    Set-Content $envPath $content -NoNewline
    Write-Success ".env updated"

    # Clear config cache to apply new APP_URL
    Write-Status "Clearing config cache..."
    php artisan config:clear --quiet 2>$null
    Write-Success "Config cache cleared"
} else {
    Write-Warn ".env file not found, skipping update"
}

# --- Display Server Info ---
Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  Laravel Development Server" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "  App URL:  http://${ip}:${Port}" -ForegroundColor White
Write-Host "  Local:    http://127.0.0.1:${Port}" -ForegroundColor Gray
Write-Host ""
Write-Host "  Press Ctrl+C to stop" -ForegroundColor DarkGray
Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# --- Open Browser ---
if ($OpenBrowser) {
    Start-Process "http://${ip}:${Port}"
}

# --- Start Server ---
php artisan serve --host=0.0.0.0 --port=$Port
