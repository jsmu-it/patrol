<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Project;

echo "--- USER 1001 ---\n";
$user = User::where('username', '1001')->first();
echo "ID: {$user->id}\n";
echo "Active Project ID: {$user->active_project_id}\n";

echo "\n--- PROJECT 4 ---\n";
$p4 = Project::find(4);
if ($p4) {
    echo "ID: 4, Name: {$p4->name}\n";
} else {
    echo "Project 4 not found.\n";
}

echo "\n--- ALL PROJECTS ---\n";
foreach(Project::all() as $p) {
    echo "ID: {$p->id}, Name: {$p->name}\n";
}
