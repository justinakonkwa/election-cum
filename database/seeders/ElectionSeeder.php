<?php

namespace Database\Seeders;

use App\Enums\ElectionStatus;
use App\Models\Election;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ElectionSeeder extends Seeder
{
    public function run(): void
    {
        $election = Election::query()->firstOrNew(['slug' => 'cum-2026']);

        $election->fill([
            'name' => 'Élections CUM 2026',
            'description' => 'Élection du nouveau comité du Club Univers Médical. L\'électeur choisit un candidat par poste.',
            'institution' => 'Université Officielle de Bukavu',
            'organization' => 'Club Univers Médical',
        ]);

        $deadline = Carbon::parse('2026-10-04 23:59:59', 'Africa/Lubumbashi');

        if (! $election->exists) {
            $election->fill([
                'start_at' => '2026-10-03 19:00:00',
                'end_at' => $deadline,
                'timezone' => 'Africa/Lubumbashi',
                'status' => ElectionStatus::Scheduled,
                'voting_type' => Election::TYPE_CANDIDATES,
                'results_visible_before_close' => false,
                'is_public_default' => true,
            ]);
        }

        $election->save();

        if (now('Africa/Lubumbashi')->lte($deadline)) {
            DB::table('elections')->where('slug', 'cum-2026')->update([
                'end_at' => '2026-10-04 23:59:59',
                'status' => ElectionStatus::Open->value,
                'closed_at' => null,
                'closed_by' => null,
            ]);
        }
    }
}
