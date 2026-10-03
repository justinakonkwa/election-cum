<?php

namespace Database\Seeders;

use App\Enums\CandidateStatus;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $election = Election::query()->where('slug', 'cum-2026')->firstOrFail();

        Position::query()
            ->where('election_id', $election->id)
            ->where('name', 'Département de Recherche')
            ->update(['name' => 'Directeur du département de recherche']);

        Position::query()
            ->where('election_id', $election->id)
            ->where('name', 'Département de revue-communication')
            ->update(['name' => 'Directeur du département des revues et communication']);

        Position::query()
            ->where('election_id', $election->id)
            ->where('name', 'Département de conférence, séminaire et débat')
            ->update(['name' => 'Directrice du département de conférence, séminaire et débat']);

        Position::query()
            ->where('election_id', $election->id)
            ->where('name', 'Département des langues')
            ->update(['name' => 'Directeur du département des langues']);

        $rows = [
            ['Président', null, 'ELDER', 'BEMBA', 'Raul', 'images/candidates/elder-bemba-raul.jpg'],
            ['VPE', 'Vice-président externe', 'MUSHAGALUSA', 'CIRIMWAMI', 'Blaise', 'images/candidates/mushagalusa-cirimwami-blaise.jpg'],
            ['VPI', 'Vice-président interne', 'NAOMIE', 'RIZIKI', 'Douce', 'images/candidates/naomie-riziki-douce.jpg'],
            ['Secrétaire général', null, 'RHUSHENGE', 'KAMANZI', 'Jeanluc', 'images/candidates/rhushenge-kamanzi-jeanluc.jpg'],
            ['Trésorière', null, 'SARAH', null, 'MUGABO', 'images/candidates/sarah-mugabo.jpg'],
            ['Caissière', null, 'BAZOLA', 'MASAKU', 'Véronique', 'images/candidates/bazola-masaku-veronique.jpg'],
            ['Directeur du département de recherche', null, 'OSCAR', null, 'NYAKASANE', 'images/candidates/oscar-nyakasane.jpg'],
            ['Directeur du département des revues et communication', null, 'ALLENGA', 'PESSY', 'Nestor', 'images/candidates/allenga-pessy-nestor.jpg'],
            ['Directrice du département de conférence, séminaire et débat', null, 'IRAGI', 'NFUNDIKO', 'Kenaya', 'images/candidates/iragi-nfundiko-kenaya.jpg'],
            ['Directeur du département des langues', null, 'ELONGO', 'LWAMBO', 'John', 'images/candidates/elongo-lwambo-john.jpg'],
        ];

        foreach ($rows as $index => [$positionName, $description, $firstName, $postName, $lastName, $photo]) {
            $position = Position::query()->updateOrCreate(
                [
                    'election_id' => $election->id,
                    'name' => $positionName,
                ],
                [
                    'description' => $description,
                    'display_order' => $index + 1,
                    'is_active' => true,
                ],
            );

            Candidate::query()->updateOrCreate(
                [
                    'election_id' => $election->id,
                    'position_id' => $position->id,
                ],
                [
                    'first_name' => $firstName,
                    'post_name' => $postName,
                    'last_name' => $lastName,
                    'photo_path' => $photo,
                    'status' => CandidateStatus::Active,
                    'display_order' => 1,
                ],
            );
        }
    }
}
