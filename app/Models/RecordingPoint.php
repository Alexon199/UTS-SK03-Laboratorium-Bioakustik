<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordingPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'recording_id',
        'location_name',
        'latitude',
        'longitude',
        'altitude',
        'weather',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'altitude' => 'integer',
        ];
    }

    /** Kebalikan dari Recording::point(). */
    public function recording(): BelongsTo
    {
        return $this->belongsTo(Recording::class);
    }
}
