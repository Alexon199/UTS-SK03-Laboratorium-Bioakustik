<?php

namespace Database\Factories;

use App\Models\Annotation;
use App\Models\Recording;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Annotation>
 */
class AnnotationFactory extends Factory
{
    protected $model = Annotation::class;

    private const SPECIES = [
        'Owa Jawa (Hylobates moloch)',
        'Elang Jawa (Nisaetus bartelsi)',
        'Cucak kutilang (Pycnonotus aurigaster)',
        'Cekakak sungai (Todiramphus chloris)',
        'Kuntul karang (Egretta sacra)',
        'Merak hijau (Pavo muticus)',
        'Ayam hutan hijau (Gallus varius)',
        'Kareo padi (Amaurornis phoenicurus)',
        'Siamang (Symphalangus syndactylus)',
        'Rangkong badak (Buceros rhinoceros)',
    ];

    public function definition(): array
    {
        $start = fake()->randomFloat(2, 0, 50);
        $low = fake()->numberBetween(300, 4000);

        return [
            'recording_id' => Recording::factory(),
            'species_name' => fake()->randomElement(self::SPECIES),
            'call_type' => fake()->randomElement(array_keys(\App\Support\Catalog::CALL_TYPES)),
            'start_time' => $start,
            'end_time' => round($start + fake()->randomFloat(2, 0.5, 8), 2),
            'low_freq_hz' => $low,
            'high_freq_hz' => $low + fake()->numberBetween(500, 4000),
            'confidence' => fake()->numberBetween(50, 99),
            'notes' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }
}
