<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$username = 'itjsmu'; // Ganti dengan username yang Anda gunakan
$user = User::where('username', $username)->first();

if ($user) {
    echo "User Found: {$user->username}\n";
    echo "Role: {$user->role}\n";
    echo "Active Project ID: " . ($user->active_project_id ?? 'NULL') . "\n";
    echo "Is Project Admin? " . ($user->isProjectAdmin() ? 'YES' : 'NO') . "\n";
    echo "Is Admin? " . ($user->isAdmin() ? 'YES' : 'NO') . "\n";
    
    // Check exact string comparison
    echo "Role match 'PROJECT_ADMIN': " . ($user->role === 'PROJECT_ADMIN' ? 'YES' : 'NO') . "\n";
} else {
    echo "User $username not found.\n";
    echo "Listing all admins:\n";
    foreach(User::whereIn('role', ['ADMIN', 'PROJECT_ADMIN', 'SUPERADMIN'])->get() as $u) {
        echo "- {$u->username} ({$u->role}) Project: {$u->active_project_id}\n";
    }
}
