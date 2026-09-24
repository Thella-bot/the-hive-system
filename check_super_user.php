<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('email', 'super@hbci.ac.ls')->first();
if ($user) {
    echo "User found:\n";
    echo "  ID: {$user->id}\n";
    echo "  Name: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Approved: " . ($user->approved_at ? 'YES' : 'NO') . "\n";
    echo "  Email verified: " . ($user->email_verified_at ? 'YES' : 'NO') . "\n";
    echo "  Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    echo "  Has password: " . ($user->password ? 'YES' : 'NO') . "\n";
} else {
    echo "User NOT found\n";
}