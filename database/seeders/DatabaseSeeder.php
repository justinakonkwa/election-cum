<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ElectionSeeder::class,
            CandidateSeeder::class,
        ]);

        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (is_string($email) && $email !== '' && is_string($password) && $password !== '') {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Administrateur',
                    'password' => $password,
                    'role' => 'admin',
                ],
            );
        }
    }
}
