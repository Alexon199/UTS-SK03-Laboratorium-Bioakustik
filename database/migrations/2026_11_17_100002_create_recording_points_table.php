<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Detail titik perekaman. Relasi ONE-TO-ONE dengan recordings:
     * kolom recording_id dibuat UNIQUE sehingga database sendiri
     * menolak titik kedua untuk rekaman yang sama.
     */
    public function up(): void
    {
        Schema::create('recording_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recording_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('location_name', 150);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->smallInteger('altitude')->nullable();  // meter di atas permukaan laut
            $table->string('weather', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recording_points');
    }
};
