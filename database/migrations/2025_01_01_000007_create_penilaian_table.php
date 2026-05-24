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
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->integer('pertemuan'); // Nomor pertemuan
            $table->integer('respect'); // Nilai respect
            $table->integer('participation'); // Nilai participation
            $table->integer('self_direction'); // Nilai self-direction
            $table->integer('caring'); // Nilai caring
            $table->integer('transfer'); // Nilai caring transfer
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
