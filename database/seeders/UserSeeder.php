<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('users')->updateOrInsert(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@bpbd-bandung.go.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'updated_at' => $now,
            ],
        );

        DB::table('users')->updateOrInsert(
            ['username' => 'petugas'],
            [
                'name' => 'Petugas Survei',
                'email' => 'petugas@bpbd-bandung.go.id',
                'password' => Hash::make('password'),
                'role' => 'petugas',
                'status' => 'active',
                'updated_at' => $now,
            ],
        );
    }
}
