<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('surveis', 'jenis_layanan')) {
            Schema::table('surveis', function (Blueprint $table) {
                $table->string('jenis_layanan')->nullable()->after('responden_id');
            });

            if (Schema::hasColumn('surveis', 'jenis_layanan_id')
                && Schema::hasTable('jenis_layanans')) {
                DB::statement(<<<'SQL'
                    UPDATE surveis
                    INNER JOIN jenis_layanans ON jenis_layanans.id = surveis.jenis_layanan_id
                    SET surveis.jenis_layanan = jenis_layanans.nama_layanan
                    WHERE surveis.jenis_layanan IS NULL
                SQL);
            }
        }

        if (! Schema::hasColumn('hasil_ikms', 'survei_id')) {
            Schema::table('hasil_ikms', function (Blueprint $table) {
                $table->foreignId('survei_id')->nullable()->after('id');
            });

            if (Schema::hasColumn('hasil_ikms', 'priode_survei_id')) {
                DB::statement(<<<'SQL'
                    UPDATE hasil_ikms
                    INNER JOIN (
                        SELECT priode_survei_id, MAX(id) AS survei_id
                        FROM surveis
                        GROUP BY priode_survei_id
                    ) AS latest_surveis ON latest_surveis.priode_survei_id = hasil_ikms.priode_survei_id
                    SET hasil_ikms.survei_id = latest_surveis.survei_id
                    WHERE hasil_ikms.survei_id IS NULL
                SQL);
            }

            Schema::table('hasil_ikms', function (Blueprint $table) {
                $table->foreign('survei_id')->references('id')->on('surveis')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hasil_ikms', 'survei_id')) {
            Schema::table('hasil_ikms', function (Blueprint $table) {
                $table->dropForeign(['survei_id']);
                $table->dropColumn('survei_id');
            });
        }

        if (Schema::hasColumn('surveis', 'jenis_layanan')) {
            Schema::table('surveis', function (Blueprint $table) {
                $table->dropColumn('jenis_layanan');
            });
        }
    }
};
