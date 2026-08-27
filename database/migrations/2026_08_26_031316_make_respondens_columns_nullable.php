<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) nama & no_hp opsional (nullable)
        Schema::table('respondens', function (Blueprint $table) {
            $table->string('nama')->nullable()->change();
            $table->string('no_hp')->nullable()->change();
        });

        // 2) ENUM -> VARCHAR(255) sesuai docs/DATABASE.md.
        //    Data lama ('SD', 'ASN', dst.) ikut terkonversi tanpa error truncate.
        DB::statement("ALTER TABLE `respondens` MODIFY COLUMN `pendidikan` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `respondens` MODIFY COLUMN `pekerjaan` VARCHAR(255) NOT NULL");

        // 3) Bersihkan kolom yang sudah diputuskan tim untuk dihapus (aman dijalankan ulang)
        Schema::table('surveis', function (Blueprint $table) {
            if (Schema::hasColumn('surveis', 'priode_survei_id'))  $table->dropColumn('priode_survei_id');
            if (Schema::hasColumn('surveis', 'jenis_layanan_id')) $table->dropColumn('jenis_layanan_id');
            if (Schema::hasColumn('surveis', 'alamat_responden')) $table->dropColumn('alamat_responden');
        });

        if (Schema::hasColumn('hasil_ikms', 'priode_survei_id')) {
            Schema::table('hasil_ikms', function (Blueprint $table) {
                $table->dropColumn('priode_survei_id');
            });
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `respondens` MODIFY COLUMN `pendidikan` ENUM('Tidak Sekolah','SD','SMP','SMA','D3','S1','S2','S3') NOT NULL");
        DB::statement("ALTER TABLE `respondens` MODIFY COLUMN `pekerjaan` ENUM('Pelajar/Mahasiswa','ASN','TNI','Polri','Swasta','Wirausaha','Lainnya') NOT NULL");

        Schema::table('respondens', function (Blueprint $table) {
            $table->string('nama')->nullable(false)->default('Masyarakat')->change();
        });
    }
};