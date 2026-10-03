<?php

namespace Database\Factories;

use App\Enums\CandidateStatus;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'election_id' => Election::factory(),
            'position_id' => Position::factory(),
            'first_name' => 'Candidat',
            'last_name' => 'TEST',
            'post_name' => null,
            'status' => CandidateStatus::Active,
            'display_order' => 1,
        ];
    }
}
