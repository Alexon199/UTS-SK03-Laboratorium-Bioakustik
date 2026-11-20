<?php

namespace Database\Seeders;

use App\Models\Annotation;
use App\Models\Recording;
use App\Models\RecordingPoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Data contoh. Koordinat dan hasil identifikasi bersifat ilustrasi untuk demo aplikasi.
 */
class RecordingSeeder extends Seeder
{
    public function run(): void
    {
        $data = $this->data();

        DB::transaction(function () use ($data) {
            foreach ($data as $item) {
                // Recording::updateOrCreate -> seeder aman dijalankan berulang (idempotent)
                $recording = Recording::updateOrCreate(
                    ['code' => $item['code']],
                    Arr::only($item, ['name', 'habitat', 'recorded_at', 'notes'])
                );

                // Relasi one-to-one: dibuat lewat relasi, recording_id terisi otomatis
                $recording->point()->updateOrCreate([], $item['point']);

                // Relasi one-to-many: bersihkan lalu isi ulang lewat relasi
                $recording->annotations()->delete();
                $recording->annotations()->createMany($item['annotations']);
            }
        });

        // Tambahan data acak lewat factory (hanya jika belum pernah ditambahkan)
        $curatedCodes = array_column($data, 'code');
        if (Recording::whereNotIn('code', $curatedCodes)->doesntExist()) {
            Recording::factory()
                ->count(12)
                ->has(RecordingPoint::factory(), 'point')
                ->has(Annotation::factory()->count(3), 'annotations')
                ->create();
        }
    }

    private function data(): array
    {
        return [
            [
                'code' => 'HP-GP-001',
                'name' => 'Fajar di jalur Cibodas',
                'habitat' => 'hutan_pegunungan',
                'recorded_at' => '2026-09-14',
                'notes' => 'Perekaman 05.30-06.30, tegakan rasamala dominan.',
                'point' => [
                    'location_name' => 'Jalur Cibodas - Telaga Biru',
                    'latitude' => -6.7456,
                    'longitude' => 107.0054,
                    'altitude' => 1450,
                    'weather' => 'Berkabut',
                ],
                'annotations' => [
                    ['species_name' => 'Owa Jawa (Hylobates moloch)', 'call_type' => 'song', 'start_time' => 2.5, 'end_time' => 48.0, 'low_freq_hz' => 400, 'high_freq_hz' => 1800, 'confidence' => 92, 'notes' => 'Duet jantan-betina.'],
                    ['species_name' => 'Elang Jawa (Nisaetus bartelsi)', 'call_type' => 'call', 'start_time' => 61.2, 'end_time' => 63.0, 'low_freq_hz' => 1200, 'high_freq_hz' => 4200, 'confidence' => 80, 'notes' => null],
                    ['species_name' => 'Burung-madu sriganti (Cinnyris jugularis)', 'call_type' => 'song', 'start_time' => 70.0, 'end_time' => 76.5, 'low_freq_hz' => 3000, 'high_freq_hz' => 7500, 'confidence' => 74, 'notes' => 'Latar belakang aliran air.'],
                ],
            ],
            [
                'code' => 'MG-SB-002',
                'name' => 'Senja mangrove Wonorejo',
                'habitat' => 'mangrove',
                'recorded_at' => '2026-09-20',
                'notes' => 'Air surut, perekaman dari jembatan pengamatan.',
                'point' => [
                    'location_name' => 'Ekowisata Mangrove Wonorejo',
                    'latitude' => -7.3040,
                    'longitude' => 112.8319,
                    'altitude' => 2,
                    'weather' => 'Mendung',
                ],
                'annotations' => [
                    ['species_name' => 'Kuntul karang (Egretta sacra)', 'call_type' => 'call', 'start_time' => 5.0, 'end_time' => 7.8, 'low_freq_hz' => 500, 'high_freq_hz' => 2500, 'confidence' => 85, 'notes' => null],
                    ['species_name' => 'Cekakak sungai (Todiramphus chloris)', 'call_type' => 'song', 'start_time' => 14.4, 'end_time' => 22.0, 'low_freq_hz' => 2200, 'high_freq_hz' => 6000, 'confidence' => 88, 'notes' => null],
                    ['species_name' => 'Kowak-malam abu (Nycticorax nycticorax)', 'call_type' => 'alarm', 'start_time' => 40.0, 'end_time' => 41.5, 'low_freq_hz' => 700, 'high_freq_hz' => 2600, 'confidence' => 69, 'notes' => 'Terganggu perahu lewat.'],
                ],
            ],
            [
                'code' => 'SV-BL-003',
                'name' => 'Savana Bekol malam hari',
                'habitat' => 'savana',
                'recorded_at' => '2026-10-02',
                'notes' => 'Perekam otomatis dipasang 18.00-22.00.',
                'point' => [
                    'location_name' => 'Savana Bekol, TN Baluran',
                    'latitude' => -7.8350,
                    'longitude' => 114.4390,
                    'altitude' => 20,
                    'weather' => 'Cerah',
                ],
                'annotations' => [
                    ['species_name' => 'Ayam hutan hijau (Gallus varius)', 'call_type' => 'song', 'start_time' => 3.0, 'end_time' => 5.2, 'low_freq_hz' => 800, 'high_freq_hz' => 3200, 'confidence' => 90, 'notes' => null],
                    ['species_name' => 'Cabak kota (Caprimulgus affinis)', 'call_type' => 'song', 'start_time' => 30.0, 'end_time' => 58.0, 'low_freq_hz' => 1500, 'high_freq_hz' => 4800, 'confidence' => 83, 'notes' => 'Nyanyian berulang.'],
                ],
            ],
            [
                'code' => 'RW-PN-004',
                'name' => 'Rawa Pening pagi berangin',
                'habitat' => 'rawa',
                'recorded_at' => '2026-10-09',
                'notes' => 'Angin cukup kencang, derau frekuensi rendah tinggi.',
                'point' => [
                    'location_name' => 'Tepi Rawa Pening, Ambarawa',
                    'latitude' => -7.2780,
                    'longitude' => 110.4170,
                    'altitude' => 463,
                    'weather' => 'Berangin',
                ],
                'annotations' => [
                    ['species_name' => 'Bambangan merah (Ixobrychus cinnamomeus)', 'call_type' => 'call', 'start_time' => 12.0, 'end_time' => 14.0, 'low_freq_hz' => 300, 'high_freq_hz' => 1200, 'confidence' => 61, 'notes' => 'Ragu karena derau angin.'],
                    ['species_name' => 'Kareo padi (Amaurornis phoenicurus)', 'call_type' => 'contact', 'start_time' => 25.5, 'end_time' => 29.0, 'low_freq_hz' => 600, 'high_freq_hz' => 2800, 'confidence' => 87, 'notes' => null],
                ],
            ],
            [
                'code' => 'SG-CS-005',
                'name' => 'Hulu sungai dini hari',
                'habitat' => 'sungai',
                'recorded_at' => '2026-10-18',
                'notes' => null,
                'point' => [
                    'location_name' => 'Situ Cisanti, Pangalengan',
                    'latitude' => -7.2200,
                    'longitude' => 107.6800,
                    'altitude' => 1500,
                    'weather' => 'Cerah',
                ],
                'annotations' => [],
            ],
            [
                'code' => 'HH-KR-006',
                'name' => 'Hutan hujan Kerinci pagi',
                'habitat' => 'hutan_hujan',
                'recorded_at' => '2026-10-25',
                'notes' => 'Transek 1 km, tiga titik berhenti.',
                'point' => [
                    'location_name' => 'Resor Kerinci, TN Kerinci Seblat',
                    'latitude' => -1.6970,
                    'longitude' => 101.2640,
                    'altitude' => 1500,
                    'weather' => 'Berawan',
                ],
                'annotations' => [
                    ['species_name' => 'Siamang (Symphalangus syndactylus)', 'call_type' => 'song', 'start_time' => 0.0, 'end_time' => 95.0, 'low_freq_hz' => 200, 'high_freq_hz' => 1500, 'confidence' => 95, 'notes' => 'Nyanyian panjang kelompok.'],
                    ['species_name' => 'Rangkong badak (Buceros rhinoceros)', 'call_type' => 'call', 'start_time' => 110.0, 'end_time' => 114.0, 'low_freq_hz' => 400, 'high_freq_hz' => 2000, 'confidence' => 78, 'notes' => null],
                    ['species_name' => 'Murai batu (Copsychus malabaricus)', 'call_type' => 'song', 'start_time' => 130.0, 'end_time' => 141.0, 'low_freq_hz' => 1800, 'high_freq_hz' => 7000, 'confidence' => 82, 'notes' => null],
                ],
            ],
        ];
    }
}
