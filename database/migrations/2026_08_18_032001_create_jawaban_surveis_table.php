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
        Schema::create('jawaban_surveis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survei_id')->constrained('surveis')->onDelete('cascade');
            $table->foreignId('unsur_pelayanan_id')->constrained('unsur_pelayanans')->onDelete('cascade');
            $table->double('nilai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_surveis');
    }
};
