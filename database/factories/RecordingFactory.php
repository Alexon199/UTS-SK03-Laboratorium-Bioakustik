<?php

namespace Database\Factories;

use App\Models\Recording;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recording>
 */
class RecordingFactory extends Factory
{
    protected $model = Recording::class;

    public function definition(): array
    {
        return [
            'code' => 'REC-'.fake()->unique()->numerify('2026-####'),
            'name' => fake()->randomElement(['Sesi pagi', 'Sesi senja', 'Sesi malam', 'Transek']).' '.fake()->city(),
            'habitat' => fake()->randomElement(array_keys(\App\Support\Catalog::HABITATS)),
            'recorded_at' => fake()->dateTimeBetween('-150 days', '-1 day')->format('Y-m-d'),
            'notes' => fake()->boolean(60) ? fake()->sentence() : null,
        ];
    }
}
