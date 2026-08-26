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
        Schema::create('saran_kritiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survei_id')->constrained('surveis')->onDelete('cascade')->onUpdate('cascade');
            $table->text('saran')->nullable();
            $table->enum('status', ['baru', 'ditinjau', 'selesai'])->default('baru');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saran_kritiks');
    }
};
