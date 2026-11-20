<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordingFilterRequest;
use App\Http\Requests\RecordingRequest;
use App\Models\Recording;
use App\Services\RecordingService;
use App\Support\Catalog;
use App\Support\ChartBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Resource Controller: 7 method standar (index, create, store, show, edit, update, destroy)
 * ditambah export. Controller tipis: terima request, panggil service, kembalikan response.
 */
class RecordingController extends Controller
{
    // Service di-inject otomatis oleh service container Laravel.
    public function __construct(private readonly RecordingService $service)
    {
    }

    /** GET /recordings : daftar + pencarian + filter + urutan + pagination */
    public function index(RecordingFilterRequest $request): View
    {
        $filters = $request->validated();
        $recordings = $this->service->paginate($filters);

        return view('recordings.index', [
            'recordings' => $recordings,
            'stats' => $this->service->stats(),
            'filters' => $filters,
            'chips' => $this->filterChips($filters),
            'maxAnnotations' => (int) ($recordings->getCollection()->max('annotations_count') ?? 0),
            'habitats' => Catalog::HABITATS,
            'callTypes' => Catalog::CALL_TYPES,
            'sorts' => RecordingService::SORTS,
            'perPageOptions' => RecordingService::PER_PAGE,
        ]);
    }

    /** GET /recordings/export : unduh CSV sesuai pencarian/filter yang aktif */
    public function export(RecordingFilterRequest $request): StreamedResponse
    {
        $filters = $request->validated();
        $filename = 'rekaman-bioakustik-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar

            foreach ($this->service->exportRows($filters) as $row) {
                fputcsv($out, $row, ',', '"', '\\');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** GET /recordings/create : form kosong */
    public function create(): View
    {
        return view('recordings.create', $this->formData(new Recording));
    }

    /** POST /recordings : validasi (RecordingRequest) -> simpan (service + transaksi) */
    public function store(RecordingRequest $request): RedirectResponse
    {
        $recording = $this->service->create($request->validated());

        return redirect()
            ->route('recordings.show', $recording)
            ->with('success', "Rekaman {$recording->code} berhasil dibuat.");
    }

    /** GET /recordings/{recording} : detail + grafik */
    public function show(Recording $recording): View
    {
        $recording = $this->service->load($recording);

        return view('recordings.show', [
            'recording' => $recording,
            'summary' => $this->service->summary($recording),
            'spectro' => ChartBuilder::spectrogram($this->service->spectrogramRows($recording)),
            'typeBars' => ChartBuilder::bars($this->service->callTypeBreakdown($recording)),
            'neighbors' => $this->service->neighbors($recording),
        ]);
    }

    /** GET /recordings/{recording}/edit : form terisi data lama */
    public function edit(Recording $recording): View
    {
        return view('recordings.edit', $this->formData($this->service->load($recording)));
    }

    /** PUT /recordings/{recording} */
    public function update(RecordingRequest $request, Recording $recording): RedirectResponse
    {
        $this->service->update($recording, $request->validated());

        return redirect()
            ->route('recordings.show', $recording)
            ->with('success', "Rekaman {$recording->code} berhasil diperbarui.");
    }

    /** DELETE /recordings/{recording} */
    public function destroy(Recording $recording): RedirectResponse
    {
        $code = $recording->code;

        $this->service->delete($recording);

        return redirect()
            ->route('recordings.index')
            ->with('success', "Rekaman {$code} beserta titik dan anotasinya telah dihapus.");
    }

    /** Data yang sama dibutuhkan form create dan edit. */
    private function formData(Recording $recording): array
    {
        return [
            'recording' => $recording,
            'habitats' => Catalog::HABITATS,
            'callTypes' => Catalog::CALL_TYPES,
            'speciesSuggestions' => $this->service->speciesSuggestions(),
        ];
    }

    /**
     * Chip filter aktif. Tiap chip membawa URL yang sama persis tetapi tanpa filter itu,
     * sehingga pengguna bisa melepas filter satu per satu.
     *
     * @return array<int, array{label: string, url: string}>
     */
    private function filterChips(array $filters): array
    {
        $labels = [
            'q' => fn ($v) => 'Cari: “'.$v.'”',
            'habitat' => fn ($v) => 'Habitat: '.(Catalog::HABITATS[$v] ?? $v),
            'call_type' => fn ($v) => 'Suara: '.(Catalog::CALL_TYPES[$v] ?? $v),
            'from' => fn ($v) => 'Dari '.$v,
            'to' => fn ($v) => 'Sampai '.$v,
        ];

        $chips = [];
        foreach ($labels as $key => $label) {
            if (filled($filters[$key] ?? null)) {
                $chips[] = [
                    'label' => $label($filters[$key]),
                    'url' => route('recordings.index', array_diff_key($filters, [$key => true])),
                ];
            }
        }

        return $chips;
    }
}
