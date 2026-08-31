<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('hasil_ikms', 'priode_survei_id')) {
            DB::statement('ALTER TABLE `hasil_ikms` MODIFY COLUMN `priode_survei_id` BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        // Legacy rows may contain null after this migration, so it cannot be safely reversed.
    }
};
