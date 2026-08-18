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
        Schema::create('hasil_ikms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('priode_survei_id')->constrained('priode_surveis')->onDelete('cascade');
            $table->double('nilai_skm');
            $table->double('nilai_ikm');
            $table->enum('mutu_pelayanan', ['A', 'B', 'C', 'D'])->default('D');  
            $table->string('kinerja_pelayanan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_ikms');
    }
};
