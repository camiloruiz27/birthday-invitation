<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoupleUsersSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->users() as $user) {
            if (! $user['name'] || ! $user['email'] || ! $user['password'] || str_starts_with($user['password'], 'CHANGE_ME')) {
                $this->command?->warn('Couple user skipped because name, email or a real password is missing in .env.');
                continue;
            }

            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                ]
            );
        }
    }

    private function users(): array
    {
        return [
            [
                'name' => env('COUPLE_USER_ONE_NAME'),
                'email' => env('COUPLE_USER_ONE_EMAIL'),
                'password' => env('COUPLE_USER_ONE_PASSWORD'),
            ],
            [
                'name' => env('COUPLE_USER_TWO_NAME'),
                'email' => env('COUPLE_USER_TWO_EMAIL'),
                'password' => env('COUPLE_USER_TWO_PASSWORD'),
            ],
            [
                'name' => env('COUPLE_TEST_USER_NAME'),
                'email' => env('COUPLE_TEST_USER_EMAIL'),
                'password' => env('COUPLE_TEST_USER_PASSWORD'),
            ],
        ];
    }
}
