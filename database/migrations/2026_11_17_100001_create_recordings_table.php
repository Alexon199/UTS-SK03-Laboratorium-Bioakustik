<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel induk: satu baris = satu sesi rekaman lapangan.
     */
    public function up(): void
    {
        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();          // kode rekaman, mis. HP-GG-001
            $table->string('name', 150);                   // nama rekaman
            $table->string('habitat', 50)->index();        // kunci habitat (lihat Recording::HABITATS)
            $table->date('recorded_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordings');
    }
};
