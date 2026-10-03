<?php

namespace Database\Factories;

use App\Enums\ElectionStatus;
use App\Models\Election;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Election>
 */
class ElectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => 'Élections CUM 2026',
            'description' => 'Élection du comité du Club Univers Médical.',
            'institution' => 'Université Officielle de Bukavu',
            'organization' => 'Club Univers Médical',
            'start_at' => '2026-10-03 19:00:00',
            'end_at' => '2026-10-03 23:59:59',
            'timezone' => 'Africa/Lubumbashi',
            'status' => ElectionStatus::Open,
            'voting_type' => Election::TYPE_CANDIDATES,
            'results_visible_before_close' => false,
            'is_public_default' => true,
        ];
    }
}
