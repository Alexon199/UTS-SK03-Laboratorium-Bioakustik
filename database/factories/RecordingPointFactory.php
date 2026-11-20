<?php

namespace Database\Factories;

use App\Models\Recording;
use App\Models\RecordingPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecordingPoint>
 */
class RecordingPointFactory extends Factory
{
    protected $model = RecordingPoint::class;

    public function definition(): array
    {
        return [
            'recording_id' => Recording::factory(),
            'location_name' => fake()->randomElement(['Jalur', 'Pos', 'Blok', 'Resor']).' '.fake()->streetName(),
            'latitude' => fake()->randomFloat(7, -8.5, 2.0),
            'longitude' => fake()->randomFloat(7, 95.5, 119.5),
            'altitude' => fake()->numberBetween(0, 2200),
            'weather' => fake()->randomElement(['Cerah', 'Berawan', 'Mendung', 'Gerimis', 'Berkabut', null]),
        ];
    }
}
