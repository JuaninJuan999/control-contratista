<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['username' => 'TIC'],
            [
                'name' => 'Superadministrador TIC',
                'email' => 'tic@control-contratista.local',
                'password' => Hash::make('SIRT123'),
            ]
        );
    }
}
