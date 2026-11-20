<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Anotasi suara. Relasi ONE-TO-MANY: satu rekaman boleh punya banyak anotasi,
     * sehingga recording_id TIDAK unique.
     */
    public function up(): void
    {
        Schema::create('annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recording_id')->constrained()->cascadeOnDelete();
            $table->string('species_name', 150)->index();
            $table->string('call_type', 30);                       // song, call, alarm, ...
            $table->decimal('start_time', 8, 2);                   // detik dari awal rekaman
            $table->decimal('end_time', 8, 2);
            $table->unsignedInteger('low_freq_hz')->nullable();
            $table->unsignedInteger('high_freq_hz')->nullable();
            $table->unsignedTinyInteger('confidence')->nullable(); // 0-100 (%)
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['recording_id', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotations');
    }
};
