<?php

namespace Database\Factories;

use App\Enums\VoterStatus;
use App\Models\Election;
use App\Models\Voter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voter>
 */
class VoterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'matricule' => 'TEST'.fake()->unique()->numerify('####'),
            'last_name' => 'TEST',
            'post_name' => null,
            'first_name' => 'Electeur',
            'faculty' => 'Médecine',
            'promotion' => 'G1',
            'status' => VoterStatus::Eligible,
            'has_voted' => false,
        ];
    }
}
