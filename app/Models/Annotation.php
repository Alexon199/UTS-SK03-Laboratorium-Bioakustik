<?php

namespace App\Models;

use App\Support\Catalog;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Annotation extends Model
{
    use HasFactory;

    /** Jenis suara (kunci => label). Sumber aslinya ada di App\Support\Catalog. */
    public const CALL_TYPES = Catalog::CALL_TYPES;

    protected $fillable = [
        'recording_id',
        'species_name',
        'call_type',
        'start_time',
        'end_time',
        'low_freq_hz',
        'high_freq_hz',
        'confidence',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'decimal:2',
            'end_time' => 'decimal:2',
            'low_freq_hz' => 'integer',
            'high_freq_hz' => 'integer',
            'confidence' => 'integer',
        ];
    }

    /** Kebalikan dari Recording::annotations(). */
    public function recording(): BelongsTo
    {
        return $this->belongsTo(Recording::class);
    }

    protected function callTypeLabel(): Attribute
    {
        return Attribute::get(fn () => Catalog::CALL_TYPES[$this->call_type] ?? $this->call_type);
    }

    /** Durasi anotasi dalam detik. */
    protected function duration(): Attribute
    {
        return Attribute::get(fn () => round((float) $this->end_time - (float) $this->start_time, 2));
    }
}
