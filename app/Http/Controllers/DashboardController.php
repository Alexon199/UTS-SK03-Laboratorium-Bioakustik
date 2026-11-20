<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Support\ChartBuilder;
use Illuminate\View\View;

/** Dashboard: service menghitung angka, ChartBuilder menghitung geometri, view menggambar. */
class DashboardController extends Controller
{
    public function __invoke(DashboardService $service): View
    {
        $data = $service->overview();

        $topSpecies = array_map(
            fn (array $row) => $row + ['href' => route('recordings.index', ['q' => $row['label']])],
            $data['top_species']
        );

        $points = array_map(
            fn (array $point) => $point + ['href' => route('recordings.show', $point['recording_id'])],
            $data['points']
        );

        $kpi = $data['kpis'];

        return view('dashboard.index', [
            'cards' => [
                ['label' => 'Rekaman', 'value' => $kpi['recordings'], 'icon' => 'wave', 'hint' => 'sesi lapangan tercatat', 'tone' => 0],
                ['label' => 'Anotasi suara', 'value' => $kpi['annotations'], 'icon' => 'tag', 'hint' => 'penanda suara pada rekaman', 'tone' => 1],
                ['label' => 'Spesies', 'value' => $kpi['species'], 'icon' => 'feather', 'hint' => 'nama spesies berbeda', 'tone' => 3],
                ['label' => 'Rata-rata keyakinan', 'value' => $kpi['confidence'] ?? 0, 'suffix' => '%', 'icon' => 'check', 'hint' => 'identifikasi anotasi', 'tone' => 2],
            ],
            'habitatCount' => $kpi['habitats'],
            'monthly' => ChartBuilder::area($data['monthly']),
            'habitat' => ChartBuilder::donut($data['habitats']),
            'callTypes' => ChartBuilder::bars($data['call_types']),
            'topSpecies' => ChartBuilder::bars($topSpecies),
            'map' => ChartBuilder::map($points),
            'recent' => $data['recent'],
        ]);
    }
}
