<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpeciesFilterRequest;
use App\Services\SpeciesService;
use Illuminate\View\View;

/** Rekap spesies dari seluruh anotasi, bisa dicari dan diurutkan. */
class SpeciesController extends Controller
{
    public function __construct(private readonly SpeciesService $service)
    {
    }

    public function index(SpeciesFilterRequest $request): View
    {
        $filters = $request->validated();

        return view('species.index', [
            'species' => $this->service->paginate($filters),
            'totalAnnotations' => $this->service->totalAnnotations(),
            'filters' => $filters,
            'sorts' => SpeciesService::SORTS,
        ]);
    }
}
