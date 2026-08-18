<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hasil_ikm_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unsur_pelayanan_id')->constrained('unsur_pelayanans')->onDelete('cascade');
            $table->foreignId('hasil_ikm_id')->constrained('hasil_ikms')->onDelete('cascade');
            $table->integer('jumlah_responden');
            $table->double('nilai_rata_rata');
            $table->double('bobot_nilai');
            $table->double('nrr_tertimbang');
            $table->enum('mutu_unsur', ['A', 'B', 'C', 'D'])->default('D');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_ikm_details');
    }
};
