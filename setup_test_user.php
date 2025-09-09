<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "Checking existing users...\n";
$userCount = User::count();
echo "Users count: $userCount\n";

if ($userCount == 0) {
    echo "Creating test user...\n";
    $user = User::create([
        'fname' => 'Test',
        'lname' => 'Host',
        'email' => 'host@example.com',
        'password' => bcrypt('password'),
        'user_id' => '1111'
    ]);
    echo "User created successfully with ID: {$user->id}\n";
} else {
    echo "Existing users:\n";
    $users = User::select('id', 'fname', 'lname', 'email', 'user_id')->get();
    foreach ($users as $user) {
        echo "{$user->id}: {$user->fname} {$user->lname} ({$user->email}) - user_id: {$user->user_id}\n";
    }
}

echo "Done.\n";