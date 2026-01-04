<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$site = App\Models\Site::where('domain', 'eta-tv.com')->first();
if ($site) {
    echo "Site ID: " . $site->id . "\n";
    echo "Site Name: " . $site->name . "\n";
    echo "Site Slug: " . $site->slug . "\n";
    echo "Login Type: " . $site->login_type . "\n";
    echo "Test Admin: " . json_encode($site->test_admin) . "\n\n";
    
    echo "=== Users ===\n";
    $users = App\Models\User::where('site_id', $site->id)->get();
    foreach ($users as $user) {
        echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Username: {$user->username}, Role: {$user->role}\n";
    }
} else {
    echo "Site not found\n";
}

