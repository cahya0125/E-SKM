<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('saran_kritiks')->delete();
        DB::table('jawaban_surveis')->delete();
        DB::table('hasil_ikm_details')->delete();
        DB::table('hasil_ikms')->delete();
        DB::table('surveis')->delete();
        DB::table('unsur_pelayanans')->delete();
        DB::table('respondens')->delete();
        DB::table('priode_surveis')->delete();
        DB::table('jenis_layanans')->delete();

        $this->call([
            UserSeeder::class,
            MasterDataSeeder::class,
            SurveySimulationSeeder::class,
        ]);
    }
}
