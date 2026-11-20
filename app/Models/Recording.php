<?php

namespace App\Models;

use App\Support\Catalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Recording extends Model
{
    use HasFactory;

    /** Daftar habitat (kunci => label). Sumber aslinya ada di App\Support\Catalog. */
    public const HABITATS = Catalog::HABITATS;

    protected $fillable = [
        'code',
        'name',
        'habitat',
        'recorded_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'date',
        ];
    }

    /* ------------------------------------------------------------------
     | Relasi
     | ------------------------------------------------------------------ */

    /** One-to-one: satu rekaman punya satu titik perekaman. */
    public function point(): HasOne
    {
        return $this->hasOne(RecordingPoint::class);
    }

    /** One-to-many: satu rekaman punya banyak anotasi. */
    public function annotations(): HasMany
    {
        return $this->hasMany(Annotation::class);
    }

    /* ------------------------------------------------------------------
     | Accessor
     | ------------------------------------------------------------------ */

    protected function habitatLabel(): Attribute
    {
        return Attribute::get(fn () => Catalog::HABITATS[$this->habitat] ?? $this->habitat);
    }

    /* ------------------------------------------------------------------
     | Query scope
     | ------------------------------------------------------------------ */

    /**
     * Pencarian + filter dikumpulkan di satu tempat. Tiap kondisi hanya aktif bila diisi (when).
     *
     * @param  array<string, mixed>  $filters  q, habitat, call_type, from, to
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            // Pencarian bebas: kode, nama, lokasi (relasi point), atau spesies (relasi annotations)
            ->when($filters['q'] ?? null, function (Builder $query, string $term) {
                $like = '%'.$term.'%';

                $query->where(function (Builder $query) use ($like) {
                    $query->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhereHas('point', fn (Builder $q) => $q->where('location_name', 'like', $like))
                        ->orWhereHas('annotations', fn (Builder $q) => $q->where('species_name', 'like', $like));
                });
            })
            ->when($filters['habitat'] ?? null, fn (Builder $query, string $habitat) => $query->where('habitat', $habitat))
            ->when($filters['call_type'] ?? null, fn (Builder $query, string $type) => $query->whereHas(
                'annotations',
                fn (Builder $q) => $q->where('call_type', $type)
            ))
            ->when($filters['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('recorded_at', '>=', $date))
            ->when($filters['to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('recorded_at', '<=', $date));
    }

    /**
     * Urutan daftar. Nilai $sort dipilih dari daftar putih (match), jadi tidak ada input
     * pengguna yang masuk ke klausa ORDER BY.
     * 'annotations' memakai alias annotations_count dari withCount() di service.
     */
    public function scopeOrderedBy(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'oldest' => $query->orderBy('recorded_at')->orderBy('id'),
            'code' => $query->orderBy('code'),
            'name' => $query->orderBy('name'),
            'annotations' => $query->orderByDesc('annotations_count')->orderBy('code'),
            default => $query->orderByDesc('recorded_at')->orderByDesc('id'),
        };
    }
}
