<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "Checking Admin Users...\n\n";

$admins = User::where('role', 'admin')->get();

if ($admins->isEmpty()) {
    echo "❌ No admin users found!\n";
    echo "Creating default admin...\n";
    
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@ojt.edu',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'student_id' => 'ADMIN001',
    ]);
    
    echo "✅ Admin created: {$admin->email} / password\n";
} else {
    echo "Found " . $admins->count() . " admin user(s):\n\n";
    foreach ($admins as $admin) {
        echo "- {$admin->email} (ID: {$admin->id}, Role: {$admin->role})\n";
    }
}

echo "\nTesting admin login redirect...\n";
$testAdmin = User::where('role', 'admin')->first();
if ($testAdmin) {
    echo "✓ Admin user exists\n";
    echo "✓ isAdmin() method: " . ($testAdmin->isAdmin() ? 'true' : 'false') . "\n";
    echo "✓ Role value: '{$testAdmin->role}'\n";
}
