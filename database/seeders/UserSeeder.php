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

        $users = [
            [
                'username' => 'admin',
                'name' => 'Administrator',
                'email' => 'admin@bpbd-bandung.go.id',
                'role' => 'admin',
                'status' => 'active',
            ],
            [
                'username' => 'petugas',
                'name' => 'Petugas Survei',
                'email' => 'petugas@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'active',
            ],
            [
                'username' => 'helmi.ahmazan',
                'name' => 'Helmi Ahmazan',
                'email' => 'helmi.ahmazan@bpbd-bandung.go.id',
                'role' => 'admin',
                'status' => 'active',
            ],
            [
                'username' => 'dedi.kurniawan',
                'name' => 'Dedi Kurniawan',
                'email' => 'dedi.kurniawan@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'active',
            ],
            [
                'username' => 'siti.nurhaliza',
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'active',
            ],
            [
                'username' => 'agus.setiawan',
                'name' => 'Agus Setiawan',
                'email' => 'agus.setiawan@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'inactive',
            ],
            [
                'username' => 'rina.marlina',
                'name' => 'Rina Marlina',
                'email' => 'rina.marlina@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'active',
            ],
            [
                'username' => 'budi.santoso',
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@bpbd-bandung.go.id',
                'role' => 'admin',
                'status' => 'active',
            ],
            [
                'username' => 'wulan.sari',
                'name' => 'Wulan Sari',
                'email' => 'wulan.sari@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'active',
            ],
            [
                'username' => 'fajar.nugraha',
                'name' => 'Fajar Nugraha',
                'email' => 'fajar.nugraha@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'inactive',
            ],
            [
                'username' => 'lestari.wibowo',
                'name' => 'Lestari Wibowo',
                'email' => 'lestari.wibowo@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'active',
            ],
            [
                'username' => 'yusuf.hidayat',
                'name' => 'Yusuf Hidayat',
                'email' => 'yusuf.hidayat@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'active',
            ],
            [
                'username' => 'nita.permata',
                'name' => 'Nita Permata',
                'email' => 'nita.permata@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'active',
            ],
            [
                'username' => 'irfan.maulana',
                'name' => 'Irfan Maulana',
                'email' => 'irfan.maulana@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'inactive',
            ],
            [
                'username' => 'diah.puspita',
                'name' => 'Diah Puspita',
                'email' => 'diah.puspita@bpbd-bandung.go.id',
                'role' => 'petugas',
                'status' => 'active',
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['username' => $user['username']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                    'status' => $user['status'],
                    'updated_at' => $now,
                ],
            );
        }
    }
}