<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'LIKE', '%YUNNAZ%')->first();
if ($user) {
    echo "User ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    
    $resource = new \App\Http\Resources\UserResource($user);
    // Mimic Request
    $request = \Illuminate\Http\Request::create('/api/me', 'GET');
    $request->setUserResolver(fn() => $user);
    
    $data = $resource->toArray($request);
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "User not found\n";
}
