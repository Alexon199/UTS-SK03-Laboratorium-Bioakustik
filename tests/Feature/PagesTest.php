<?php

namespace Tests\Feature;

use App\Models\Annotation;
use App\Models\Recording;
use App\Models\RecordingPoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Dashboard, rekap spesies, ekspor CSV, dan urutan. */
class PagesTest extends TestCase
{
    use RefreshDatabase;

    private function makeRecording(string $code, string $habitat, array $annotations = []): Recording
    {
        $recording = Recording::factory()->create([
            'code' => $code,
            'habitat' => $habitat,
            'recorded_at' => now()->subDays(3)->toDateString(),
        ]);
        RecordingPoint::factory()->create(['recording_id' => $recording->id]);

        foreach ($annotations as $species => $callType) {
            Annotation::factory()->create([
                'recording_id' => $recording->id,
                'species_name' => $species,
                'call_type' => $callType,
            ]);
        }

        return $recording;
    }

    public function test_dashboard_renders_with_data(): void
    {
        $this->makeRecording('AAA-111', 'mangrove', ['Kuntul karang (Egretta sacra)' => 'call']);
        $this->makeRecording('BBB-222', 'savana', ['Merak hijau (Pavo muticus)' => 'song']);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rekaman per bulan')
            ->assertSee('Sebaran habitat')
            ->assertSee('Kuntul karang')
            ->assertSee('AAA-111');
    }

    public function test_dashboard_renders_when_database_is_empty(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada rekaman');
    }

    public function test_species_page_groups_and_searches(): void
    {
        $this->makeRecording('AAA-111', 'mangrove', ['Kuntul karang (Egretta sacra)' => 'call']);
        $this->makeRecording('BBB-222', 'mangrove', ['Kuntul karang (Egretta sacra)' => 'song']);
        $this->makeRecording('CCC-333', 'savana', ['Merak hijau (Pavo muticus)' => 'song']);

        $this->get(route('species.index'))
            ->assertOk()
            ->assertSee('Kuntul karang')
            ->assertSee('Merak hijau');

        $this->get(route('species.index', ['q' => 'merak']))
            ->assertOk()
            ->assertSee('Merak hijau')
            ->assertDontSee('Kuntul karang');
    }

    public function test_species_rejects_unknown_sort(): void
    {
        $this->get(route('species.index', ['sort' => 'DROP TABLE']))
            ->assertSessionHasErrors('sort');
    }

    public function test_recordings_can_be_sorted_by_annotation_count(): void
    {
        $this->makeRecording('FEW-001', 'mangrove', ['A' => 'call']);
        $this->makeRecording('MANY-002', 'mangrove', ['A' => 'call', 'B' => 'song', 'C' => 'alarm']);

        $this->get(route('recordings.index', ['sort' => 'annotations']))
            ->assertOk()
            ->assertSeeInOrder(['MANY-002', 'FEW-001']);
    }

    public function test_recordings_reject_unknown_sort_and_per_page(): void
    {
        $this->get(route('recordings.index', ['sort' => 'id; DROP TABLE recordings']))
            ->assertSessionHasErrors('sort');

        $this->get(route('recordings.index', ['per_page' => 9999]))
            ->assertSessionHasErrors('per_page');
    }

    public function test_export_csv_follows_filters(): void
    {
        $this->makeRecording('AAA-111', 'mangrove', ['Kuntul karang (Egretta sacra)' => 'call']);
        $this->makeRecording('BBB-222', 'savana', ['Merak hijau (Pavo muticus)' => 'song']);

        $response = $this->get(route('recordings.export', ['habitat' => 'savana']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Kode', $csv);        // baris judul
        $this->assertStringContainsString('BBB-222', $csv);     // lolos filter
        $this->assertStringContainsString('Merak hijau', $csv); // spesies ikut diekspor
        $this->assertStringNotContainsString('AAA-111', $csv);  // tersaring
    }

    public function test_export_route_is_not_swallowed_by_resource_show(): void
    {
        // Jika route export didaftarkan setelah Route::resource, ini akan menjadi 404 (id "export").
        $this->get('/recordings/export')->assertOk();
    }

    public function test_show_page_renders_spectrogram_for_recording_with_annotations(): void
    {
        $recording = $this->makeRecording('AAA-111', 'hutan_pegunungan', ['Owa Jawa (Hylobates moloch)' => 'song']);

        $this->get(route('recordings.show', $recording))
            ->assertOk()
            ->assertSee('Spektrogram anotasi')
            ->assertSee('Owa Jawa');
    }

    public function test_show_page_renders_for_recording_without_annotations(): void
    {
        $recording = $this->makeRecording('EMPTY-001', 'sungai');

        $this->get(route('recordings.show', $recording))
            ->assertOk()
            ->assertSee('belum memiliki anotasi');
    }

    public function test_create_and_edit_forms_render(): void
    {
        $recording = $this->makeRecording('AAA-111', 'mangrove', ['Kuntul karang (Egretta sacra)' => 'call']);

        $this->get(route('recordings.create'))->assertOk()->assertSee('Rekaman baru');
        $this->get(route('recordings.edit', $recording))->assertOk()->assertSee('AAA-111');
    }
}
