<?php

namespace App\Services;

use App\Models\Annotation;
use App\Models\Recording;
use App\Support\Catalog;
use Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Lapisan service: seluruh logika bisnis rekaman ada di sini, bukan di controller.
 *
 * Satu "simpan rekaman" menyentuh tiga tabel (recordings, recording_points, annotations),
 * maka create/update/delete dibungkus DB::transaction() supaya bersifat all-or-nothing:
 * jika salah satu langkah gagal, semua perubahan dibatalkan (rollback).
 */
class RecordingService
{
    private const RECORDING_FIELDS = ['code', 'name', 'habitat', 'recorded_at', 'notes'];

    /** Pilihan urutan di halaman daftar (kunci => label). */
    public const SORTS = [
        'newest' => 'Terbaru',
        'oldest' => 'Terlama',
        'code' => 'Kode A–Z',
        'name' => 'Nama A–Z',
        'annotations' => 'Anotasi terbanyak',
    ];

    public const PER_PAGE = [10, 25, 50];

    /* ------------------------------------------------------------------
     | Baca
     | ------------------------------------------------------------------ */

    /** Query dasar daftar: hitung anotasi + pencarian/filter + urutan. */
    private function listQuery(array $filters): Builder
    {
        return Recording::query()
            ->withCount('annotations')
            ->filter($filters)
            ->orderedBy($filters['sort'] ?? 'newest');
    }

    /**
     * Daftar rekaman dengan pencarian + filter + urutan + pagination.
     * with('point') dan withCount('annotations') mencegah masalah N+1 query di tabel.
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? self::PER_PAGE[0]);
        if (! in_array($perPage, self::PER_PAGE, true)) {
            $perPage = self::PER_PAGE[0];
        }

        return $this->listQuery($filters)
            ->with('point')
            ->paginate($perPage)
            ->withQueryString(); // pencarian/filter tetap terbawa saat pindah halaman
    }

    /** Angka ringkasan untuk strip statistik di halaman daftar. */
    public function stats(): array
    {
        return [
            'recordings' => Recording::count(),
            'annotations' => Annotation::count(),
            'species' => Annotation::query()->distinct()->count('species_name'),
        ];
    }

    /** Muat relasi yang dibutuhkan halaman detail / edit (anotasi urut waktu). */
    public function load(Recording $recording): Recording
    {
        return $recording->load([
            'point',
            'annotations' => fn ($query) => $query->orderBy('start_time'),
        ]);
    }

    /** Saran nama spesies untuk datalist di form (diambil dari anotasi yang sudah ada). */
    public function speciesSuggestions(): array
    {
        return Annotation::query()->distinct()->orderBy('species_name')->pluck('species_name')->all();
    }

    /** Rekaman sebelum/sesudah (berdasarkan id) untuk tombol navigasi di halaman detail. */
    public function neighbors(Recording $recording): array
    {
        return [
            'prev' => Recording::query()->where('id', '<', $recording->id)->orderByDesc('id')->first(['id', 'code']),
            'next' => Recording::query()->where('id', '>', $recording->id)->orderBy('id')->first(['id', 'code']),
        ];
    }

    /**
     * Ringkasan anotasi satu rekaman. Memakai relasi yang sudah dimuat (tanpa query tambahan).
     *
     * @return array{annotations: int, species: int, confidence: ?int, duration: float}
     */
    public function summary(Recording $recording): array
    {
        $annotations = $recording->annotations;
        $withConfidence = $annotations->whereNotNull('confidence');

        return [
            'annotations' => $annotations->count(),
            'species' => $annotations->pluck('species_name')->unique()->count(),
            'confidence' => $withConfidence->isNotEmpty() ? (int) round($withConfidence->avg('confidence')) : null,
            'duration' => round($annotations->sum(fn ($a) => (float) $a->end_time - (float) $a->start_time), 1),
        ];
    }

    /** Baris data untuk ChartBuilder::spectrogram(). */
    public function spectrogramRows(Recording $recording): array
    {
        return $recording->annotations->values()->map(fn ($a, $i) => [
            'idx' => $i,
            'label' => $a->species_name,
            'type' => $a->call_type,
            'type_label' => $a->call_type_label,
            'color' => Catalog::callColor($a->call_type),
            'start' => (float) $a->start_time,
            'end' => (float) $a->end_time,
            'low' => $a->low_freq_hz,
            'high' => $a->high_freq_hz,
        ])->all();
    }

    /** Jumlah anotasi per jenis suara (untuk batang di halaman detail). */
    public function callTypeBreakdown(Recording $recording): array
    {
        $counts = $recording->annotations->countBy('call_type');
        $items = [];

        foreach (Catalog::CALL_TYPES as $key => $label) {
            if (($counts[$key] ?? 0) > 0) {
                $items[] = ['label' => $label, 'value' => $counts[$key], 'color' => Catalog::callColor($key)];
            }
        }

        return $items;
    }

    /**
     * Data ekspor CSV (satu baris per rekaman), mengikuti pencarian/filter/urutan yang aktif.
     * Memakai Generator supaya controller bisa menulis baris demi baris.
     */
    public function exportRows(array $filters = []): Generator
    {
        yield [
            'Kode', 'Nama rekaman', 'Habitat', 'Tanggal rekam', 'Lokasi', 'Lintang', 'Bujur',
            'Ketinggian (mdpl)', 'Cuaca', 'Jumlah anotasi', 'Spesies', 'Catatan',
        ];

        $recordings = $this->listQuery($filters)->with(['point', 'annotations'])->get();

        foreach ($recordings as $recording) {
            yield [
                $recording->code,
                $recording->name,
                $recording->habitat_label,
                $recording->recorded_at?->format('Y-m-d'),
                $recording->point?->location_name,
                $recording->point?->latitude,
                $recording->point?->longitude,
                $recording->point?->altitude,
                $recording->point?->weather,
                $recording->annotations_count,
                $recording->annotations->pluck('species_name')->unique()->implode('; '),
                $recording->notes,
            ];
        }
    }

    /* ------------------------------------------------------------------
     | Tulis (semuanya dalam transaksi)
     | ------------------------------------------------------------------ */

    /**
     * Buat rekaman + titik perekaman + anotasi dalam satu transaksi.
     *
     * @param  array<string, mixed>  $data  hasil FormRequest::validated()
     */
    public function create(array $data): Recording
    {
        return DB::transaction(function () use ($data) {
            $recording = Recording::create(Arr::only($data, self::RECORDING_FIELDS));

            $recording->point()->create($data['point']);

            $this->syncAnnotations($recording, $data['annotations'] ?? []);

            return $recording;
        });
    }

    /**
     * Perbarui rekaman, titik, dan anotasi dalam satu transaksi.
     *
     * @param  array<string, mixed>  $data  hasil FormRequest::validated()
     */
    public function update(Recording $recording, array $data): Recording
    {
        return DB::transaction(function () use ($recording, $data) {
            $recording->update(Arr::only($data, self::RECORDING_FIELDS));

            // updateOrCreate([]) pada hasOne: ubah titik yang ada, atau buat bila belum ada
            $recording->point()->updateOrCreate([], $data['point']);

            $this->syncAnnotations($recording, $data['annotations'] ?? []);

            return $recording;
        });
    }

    /** Hapus anak-anaknya dulu secara eksplisit, lalu induknya (FK cascade sebagai pengaman kedua). */
    public function delete(Recording $recording): void
    {
        DB::transaction(function () use ($recording) {
            $recording->annotations()->delete();
            $recording->point()->delete();
            $recording->delete();
        });
    }

    /**
     * Sinkronkan anotasi dengan baris yang dikirim form:
     *  - baris dengan id milik rekaman ini  -> di-update
     *  - baris tanpa id                     -> dibuat baru
     *  - anotasi lama yang tidak dikirim    -> dihapus
     *
     * Pencarian id dibatasi lewat relasi ($recording->annotations()), jadi id milik
     * rekaman lain tidak akan pernah ikut terubah.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncAnnotations(Recording $recording, array $rows): void
    {
        $keepIds = [];

        foreach ($rows as $row) {
            $attributes = Arr::except($row, ['id']);

            if (! empty($row['id'])) {
                $existing = $recording->annotations()->whereKey($row['id'])->first();

                if ($existing) {
                    $existing->update($attributes);
                    $keepIds[] = $existing->id;

                    continue;
                }
            }

            $keepIds[] = $recording->annotations()->create($attributes)->id;
        }

        $recording->annotations()->whereNotIn('id', $keepIds)->delete();
    }
}
