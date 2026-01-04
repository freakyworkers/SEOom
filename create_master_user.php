<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MasterUser;
use Illuminate\Support\Facades\Hash;

$user = MasterUser::firstOrCreate(
    ['email' => 'master@seoom.com'],
    [
        'name' => 'Master Admin',
        'password' => Hash::make('test1234'),
        'role' => 'super_admin',
    ]
);

echo "Master user created successfully!\n";
echo "Email: master@seoom.com\n";
echo "Password: test1234\n";
echo "Role: super_admin\n";






