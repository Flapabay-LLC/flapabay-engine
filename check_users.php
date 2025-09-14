<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== Checking Users in Database ===\n";

$users = DB::table('users')->select('id', 'email')->get();

foreach($users as $user) {
    echo "ID: {$user->id}, Email: {$user->email}\n";
}

echo "\nTotal users: " . count($users) . "\n";
echo "=== User Check Complete ===\n";