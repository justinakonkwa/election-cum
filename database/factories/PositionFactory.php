<?php

namespace Database\Factories;

use App\Models\Election;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'name' => 'Poste '.fake()->unique()->word(),
            'description' => null,
            'display_order' => 1,
            'is_active' => true,
        ];
    }
}
