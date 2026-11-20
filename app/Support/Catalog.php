<?php

namespace App\Support;

/**
 * Daftar nilai tetap (habitat, jenis suara) di satu tempat.
 * Kelas ini tidak bergantung pada Laravel, jadi dipakai bersama oleh model, view, dan tes.
 *
 * Kunci disimpan di database, label hanya untuk tampilan.
 * Angka "warna" menunjuk ke palet grafik --c0 ... --c7 di CSS.
 */
final class Catalog
{
    public const HABITATS = [
        'hutan_hujan' => 'Hutan hujan tropis',
        'hutan_pegunungan' => 'Hutan pegunungan',
        'mangrove' => 'Mangrove',
        'rawa' => 'Rawa & lahan basah',
        'sungai' => 'Sungai & riparian',
        'savana' => 'Savana & padang rumput',
        'agroforestri' => 'Agroforestri & kebun',
        'perairan_laut' => 'Perairan laut',
    ];

    public const CALL_TYPES = [
        'song' => 'Nyanyian',
        'call' => 'Panggilan',
        'alarm' => 'Alarm',
        'contact' => 'Kontak',
        'drumming' => 'Drumming',
        'other' => 'Lainnya',
    ];

    /** Jenis suara -> nomor warna palet. */
    public const CALL_COLORS = [
        'song' => 1,
        'call' => 0,
        'alarm' => 4,
        'contact' => 2,
        'drumming' => 3,
        'other' => 7,
    ];

    /** Habitat -> nomor warna palet (urutan di daftar HABITATS). */
    public static function habitatColor(?string $key): int
    {
        $index = array_search($key, array_keys(self::HABITATS), true);

        return $index === false ? 7 : (int) $index;
    }

    public static function callColor(?string $key): int
    {
        return self::CALL_COLORS[$key] ?? 7;
    }
}
