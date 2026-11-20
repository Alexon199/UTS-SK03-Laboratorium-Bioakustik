<?php

namespace App\Services;

use App\Models\Annotation;
use App\Models\Recording;
use App\Models\RecordingPoint;
use App\Support\Catalog;
use App\Support\Fmt;

/**
 * Mengumpulkan angka untuk dashboard. Hasilnya array biasa yang siap dikirim ke ChartBuilder.
 * Agregasi memakai query GROUP BY di database; hanya pengelompokan bulan yang dilakukan di PHP
 * supaya tidak bergantung pada fungsi tanggal khusus MySQL atau SQLite.
 */
class DashboardService
{
    public function overview(): array
    {
        $confidence = Annotation::query()->whereNotNull('confidence')->avg('confidence');

        return [
            'kpis' => [
                'recordings' => Recording::count(),
                'annotations' => Annotation::count(),
                'species' => Annotation::query()->distinct()->count('species_name'),
                'confidence' => $confidence !== null ? (int) round((float) $confidence) : null,
                'habitats' => Recording::query()->distinct()->count('habitat'),
            ],
            'monthly' => $this->monthly(6),
            'habitats' => $this->habitats(),
            'call_types' => $this->callTypes(),
            'top_species' => $this->topSpecies(8),
            'points' => $this->points(),
            'recent' => Recording::query()
                ->with('point')
                ->withCount('annotations')
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ];
    }

    /** Jumlah rekaman per bulan untuk $months bulan terakhir (bulan tanpa data = 0). */
    private function monthly(int $months): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $perMonth = Recording::query()
            ->where('recorded_at', '>=', $start)
            ->pluck('recorded_at')
            ->countBy(fn ($date) => $date->format('Y-m'));

        $series = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $series[] = [
                'label' => Fmt::monthShort($month->month).' '.$month->format('y'),
                'value' => (int) ($perMonth[$month->format('Y-m')] ?? 0),
            ];
        }

        return $series;
    }

    private function habitats(): array
    {
        $counts = Recording::query()
            ->selectRaw('habitat, COUNT(*) as total')
            ->groupBy('habitat')
            ->pluck('total', 'habitat');

        $items = [];
        foreach (Catalog::HABITATS as $key => $label) {
            if (($counts[$key] ?? 0) > 0) {
                $items[] = ['key' => $key, 'label' => $label, 'value' => (int) $counts[$key], 'idx' => Catalog::habitatColor($key)];
            }
        }

        return $items;
    }

    private function callTypes(): array
    {
        $counts = Annotation::query()
            ->selectRaw('call_type, COUNT(*) as total')
            ->groupBy('call_type')
            ->pluck('total', 'call_type');

        $items = [];
        foreach (Catalog::CALL_TYPES as $key => $label) {
            $items[] = ['key' => $key, 'label' => $label, 'value' => (int) ($counts[$key] ?? 0), 'color' => Catalog::callColor($key)];
        }

        return $items;
    }

    private function topSpecies(int $limit): array
    {
        return Annotation::query()
            ->selectRaw('species_name, COUNT(*) as total')
            ->groupBy('species_name')
            ->orderByDesc('total')
            ->orderBy('species_name')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['label' => $row->species_name, 'value' => (int) $row->total])
            ->all();
    }

    private function points(): array
    {
        return RecordingPoint::query()
            ->with('recording:id,code,name,habitat')
            ->get()
            ->filter(fn ($point) => $point->recording !== null)
            ->map(fn ($point) => [
                'lat' => (float) $point->latitude,
                'lon' => (float) $point->longitude,
                'label' => $point->recording->name,
                'code' => $point->recording->code,
                'place' => $point->location_name,
                'recording_id' => $point->recording_id,
                'color' => Catalog::habitatColor($point->recording->habitat),
            ])
            ->values()
            ->all();
    }
}
