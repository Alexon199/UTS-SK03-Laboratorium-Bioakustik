<?php

namespace Tests\Feature;

use App\Models\Annotation;
use App\Models\Recording;
use App\Models\RecordingPoint;
use App\Services\RecordingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class RecordingCrudTest extends TestCase
{
    use RefreshDatabase;

    /** Payload valid: satu rekaman, satu titik, dua anotasi. */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'rec-001',
            'name' => 'Fajar di Cibodas',
            'habitat' => 'hutan_pegunungan',
            'recorded_at' => '2026-10-12',
            'notes' => 'Cerah',
            'point' => [
                'location_name' => 'Jalur Cibodas',
                'latitude' => '-6.7456',
                'longitude' => '107.0054',
                'altitude' => '1400',
                'weather' => 'Cerah',
            ],
            'annotations' => [
                [
                    'species_name' => 'Owa Jawa',
                    'call_type' => 'song',
                    'start_time' => '1.5',
                    'end_time' => '9',
                    'low_freq_hz' => '500',
                    'high_freq_hz' => '1800',
                    'confidence' => '90',
                ],
                [
                    'species_name' => 'Kutilang',
                    'call_type' => 'call',
                    'start_time' => '12',
                    'end_time' => '14.5',
                ],
            ],
        ], $overrides);
    }

    public function test_root_shows_dashboard(): void
    {
        $this->get('/')->assertOk()->assertSee('Dashboard');
    }

    public function test_index_lists_recordings(): void
    {
        Recording::factory()
            ->state(['code' => 'AAA-111', 'name' => 'Sesi uji'])
            ->has(RecordingPoint::factory(), 'point')
            ->create();

        $this->get(route('recordings.index'))
            ->assertOk()
            ->assertSee('AAA-111')
            ->assertSee('Sesi uji');
    }

    public function test_store_creates_recording_point_and_annotations(): void
    {
        $this->post(route('recordings.store'), $this->validPayload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        // kode dinormalkan menjadi huruf besar oleh prepareForValidation()
        $this->assertDatabaseHas('recordings', ['code' => 'REC-001', 'habitat' => 'hutan_pegunungan']);
        $this->assertDatabaseHas('recording_points', ['location_name' => 'Jalur Cibodas']);
        $this->assertDatabaseCount('annotations', 2);

        $recording = Recording::where('code', 'REC-001')->firstOrFail();
        $this->assertSame('Jalur Cibodas', $recording->point->location_name);
        $this->assertCount(2, $recording->annotations);
    }

    public function test_store_rejects_missing_point_and_invalid_annotation_time(): void
    {
        $payload = $this->validPayload();
        unset($payload['point']);
        $payload['annotations'][0]['end_time'] = '0.5'; // lebih kecil dari start_time (1.5)

        $this->post(route('recordings.store'), $payload)
            ->assertSessionHasErrors(['point', 'annotations.0.end_time']);

        $this->assertDatabaseCount('recordings', 0);
    }

    public function test_store_rejects_unpaired_or_reversed_frequency(): void
    {
        $payload = $this->validPayload();
        $payload['annotations'][0]['low_freq_hz'] = '2000';
        $payload['annotations'][0]['high_freq_hz'] = '1000';
        $payload['annotations'][1]['low_freq_hz'] = '300'; // batas atas tidak diisi

        $this->post(route('recordings.store'), $payload)
            ->assertSessionHasErrors(['annotations.0.high_freq_hz', 'annotations.1.high_freq_hz']);
    }

    public function test_code_must_be_unique(): void
    {
        Recording::factory()->create(['code' => 'REC-001']);

        $this->post(route('recordings.store'), $this->validPayload())
            ->assertSessionHasErrors('code');
    }

    public function test_search_by_species_and_filter_by_habitat(): void
    {
        Recording::factory()
            ->state(['code' => 'AAA-111', 'habitat' => 'mangrove'])
            ->has(RecordingPoint::factory(), 'point')
            ->has(Annotation::factory()->state(['species_name' => 'Kuntul karang']), 'annotations')
            ->create();

        Recording::factory()
            ->state(['code' => 'BBB-222', 'habitat' => 'savana'])
            ->has(RecordingPoint::factory(), 'point')
            ->has(Annotation::factory()->state(['species_name' => 'Merak hijau']), 'annotations')
            ->create();

        // pencarian lewat relasi annotations
        $this->get(route('recordings.index', ['q' => 'kuntul']))
            ->assertOk()
            ->assertSee('AAA-111')
            ->assertDontSee('BBB-222');

        // filter habitat
        $this->get(route('recordings.index', ['habitat' => 'savana']))
            ->assertOk()
            ->assertSee('BBB-222')
            ->assertDontSee('AAA-111');
    }

    public function test_update_syncs_annotations(): void
    {
        $recording = app(RecordingService::class)->create($this->validPayload());
        $first = $recording->annotations()->orderBy('start_time')->firstOrFail();

        $payload = $this->validPayload([
            'name' => 'Fajar di Cibodas (revisi)',
            'annotations' => [
                // baris lama (punya id) -> di-update
                ['id' => $first->id, 'species_name' => 'Owa Jawa (revisi)', 'call_type' => 'song', 'start_time' => '1.5', 'end_time' => '9'],
                // baris baru (tanpa id) -> dibuat; "Kutilang" tidak dikirim -> dihapus
                ['species_name' => 'Elang Jawa', 'call_type' => 'alarm', 'start_time' => '20', 'end_time' => '22'],
            ],
        ]);

        $this->put(route('recordings.update', $recording), $payload)
            ->assertRedirect(route('recordings.show', $recording))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('recordings', ['id' => $recording->id, 'name' => 'Fajar di Cibodas (revisi)']);
        $this->assertDatabaseHas('annotations', ['id' => $first->id, 'species_name' => 'Owa Jawa (revisi)']);
        $this->assertDatabaseHas('annotations', ['species_name' => 'Elang Jawa']);
        $this->assertDatabaseMissing('annotations', ['species_name' => 'Kutilang']);
        $this->assertDatabaseCount('annotations', 2);
        $this->assertDatabaseCount('recording_points', 1); // titik di-update, tidak digandakan
    }

    public function test_destroy_removes_recording_with_point_and_annotations(): void
    {
        $recording = app(RecordingService::class)->create($this->validPayload());

        $this->delete(route('recordings.destroy', $recording))
            ->assertRedirect(route('recordings.index'));

        $this->assertDatabaseCount('recordings', 0);
        $this->assertDatabaseCount('recording_points', 0);
        $this->assertDatabaseCount('annotations', 0);
    }

    public function test_transaction_rolls_back_when_annotation_insert_fails(): void
    {
        // Simulasikan kegagalan pada langkah terakhir (menyimpan anotasi).
        Annotation::creating(function () {
            throw new RuntimeException('simulasi gagal');
        });

        try {
            app(RecordingService::class)->create($this->validPayload());
            $this->fail('Seharusnya melempar exception.');
        } catch (RuntimeException) {
            // diharapkan
        }

        // Rekaman dan titik yang sudah sempat dibuat harus ikut dibatalkan.
        $this->assertDatabaseCount('recordings', 0);
        $this->assertDatabaseCount('recording_points', 0);
    }
}
