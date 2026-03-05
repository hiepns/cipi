<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApiUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'cipi-api@local'],
            [
                'name' => 'Cipi API',
                'password' => Hash::make(bin2hex(random_bytes(32))),
            ]
        );
    }
}
