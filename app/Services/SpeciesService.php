<?php

namespace App\Services;

use App\Models\Annotation;
use Illuminate\Pagination\LengthAwarePaginator;

/** Rekap spesies: dikelompokkan dari tabel annotations (GROUP BY species_name). */
class SpeciesService
{
    /** Pilihan urutan (kunci => label). Kunci dipetakan ke kolom lewat match, bukan dari input mentah. */
    public const SORTS = [
        'annotations' => 'Anotasi terbanyak',
        'recordings' => 'Rekaman terbanyak',
        'confidence' => 'Keyakinan tertinggi',
        'name' => 'Nama A–Z',
    ];

    private const PER_PAGE = 15;

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Annotation::query()
            ->select('species_name')
            ->selectRaw('COUNT(*) as annotations_count')
            ->selectRaw('COUNT(DISTINCT recording_id) as recordings_count')
            ->selectRaw('ROUND(AVG(confidence)) as avg_confidence')
            ->when($filters['q'] ?? null, fn ($q, string $term) => $q->where('species_name', 'like', '%'.$term.'%'))
            ->groupBy('species_name');

        match ($filters['sort'] ?? 'annotations') {
            'recordings' => $query->orderByDesc('recordings_count')->orderBy('species_name'),
            'confidence' => $query->orderByDesc('avg_confidence')->orderBy('species_name'),
            'name' => $query->orderBy('species_name'),
            default => $query->orderByDesc('annotations_count')->orderBy('species_name'),
        };

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    /** Total anotasi seluruh spesies, dipakai untuk bar "porsi" di tabel. */
    public function totalAnnotations(): int
    {
        return Annotation::count();
    }
}
