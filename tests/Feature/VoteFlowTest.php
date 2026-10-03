<?php

namespace Tests\Feature;

use App\Enums\CandidateStatus;
use App\Enums\ElectionStatus;
use App\Exceptions\VotingException;
use App\Models\Ballot;
use App\Models\BallotChoice;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Participation;
use App\Models\Position;
use App\Models\User;
use App\Models\Voter;
use App\Services\VotingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VoteFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-03 20:00:00', 'Africa/Lubumbashi'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_a_partial_ballot_is_stored_and_the_session_is_closed(): void
    {
        [, $choices] = $this->ballotWorld();
        $onlyOne = [array_key_first($choices) => $choices[array_key_first($choices)]];

        $this->get(route('vote.ballot'))
            ->assertOk()
            ->assertSee('Choisissez vos candidats')
            ->assertSee('laisser un poste vide')
            ->assertDontSee('Votre matricule')
            ->assertSee('MUKENDI')
            ->assertSee('KASONGO');

        $this->post(route('vote.review'), ['choices' => $onlyOne])
            ->assertRedirect(route('vote.confirm'));

        $this->get(route('vote.confirm'))
            ->assertOk()
            ->assertSee('MUKENDI')
            ->assertDontSee('KASONGO')
            ->assertSee('après confirmation');

        $this->post(route('vote.cast'))
            ->assertRedirect(route('vote.success'))
            ->assertSessionMissing('voter_choices')
            ->assertSessionMissing('voter_ballot');

        $this->get(route('vote.success'))
            ->assertOk()
            ->assertSee('Votre vote a été enregistré avec succès.');

        $this->get(route('vote.confirm'))
            ->assertRedirect(route('vote.ballot'));

        $this->assertSame(1, Ballot::query()->count());
        $this->assertSame(1, BallotChoice::query()->count());
        $this->assertSame(0, Participation::query()->count());
        $this->assertSame(0, Voter::query()->count());
        $this->assertFalse(Schema::hasColumn('ballots', 'voter_id'));
        $this->assertDatabaseHas('ballot_choices', [
            'position_id' => array_key_first($onlyOne),
            'candidate_id' => $onlyOne[array_key_first($onlyOne)],
        ]);
    }

    public function test_voting_is_refused_before_opening_after_closing_and_when_suspended(): void
    {
        [$election, $choices] = $this->ballotWorld();

        Carbon::setTestNow(Carbon::parse('2026-10-03 18:00:00', 'Africa/Lubumbashi'));
        $election->update(['status' => ElectionStatus::Scheduled]);

        $this->get(route('vote.ballot'))
            ->assertOk()
            ->assertSee('actuellement pas ouvert', false);

        $this->post(route('vote.review'), ['choices' => $choices])
            ->assertRedirect(route('vote.ballot'))
            ->assertSessionHas('error', 'Le scrutin n\'est actuellement pas ouvert.');

        Carbon::setTestNow(Carbon::parse('2026-10-03 20:00:00', 'Africa/Lubumbashi'));
        $election->update(['status' => ElectionStatus::Suspended]);

        $this->post(route('vote.review'), ['choices' => $choices])
            ->assertSessionHas('error', 'Le scrutin n\'est actuellement pas ouvert.');

        Carbon::setTestNow(Carbon::parse('2026-10-04 00:30:00', 'Africa/Lubumbashi'));
        $election->update(['status' => ElectionStatus::Open, 'closed_at' => null]);

        $this->get(route('vote.ballot'))
            ->assertSee('actuellement pas ouvert', false);

        $this->assertSame(0, Ballot::query()->count());
        $this->assertSame(ElectionStatus::Closed, $election->fresh()->status);
    }

    public function test_incomplete_or_invalid_choices_do_not_create_a_ballot(): void
    {
        [, $choices] = $this->ballotWorld();
        $this->from(route('vote.ballot'))
            ->post(route('vote.review'), [])
            ->assertSessionHasErrors('choices');

        $swapped = [
            array_key_first($choices) => array_values($choices)[1],
            array_key_last($choices) => array_values($choices)[0],
        ];

        $this->from(route('vote.ballot'))
            ->post(route('vote.review'), ['choices' => $swapped])
            ->assertSessionHas('error', 'Un des candidats choisis n\'est pas valide.');

        $this->assertSame(0, Ballot::query()->count());
    }

    public function test_results_stay_hidden_until_the_election_is_closed(): void
    {
        [$election, $choices] = $this->ballotWorld();
        $admin = User::factory()->create();
        app(VotingService::class)->cast($election, $choices);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Les résultats restent masqués')
            ->assertDontSee('voix');

        Election::query()->first()->update([
            'status' => ElectionStatus::Closed,
            'closed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('voix');
    }

    public function test_admin_space_requires_an_administrator(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('vote.ballot'))
            ->assertOk()
            ->assertDontSee('Votre matricule UOB');
    }

    public function test_csv_import_reports_created_duplicates_and_errors(): void
    {
        $this->ballotWorld();
        $admin = User::factory()->create();
        $csv = "matricule,nom,postnom,prenom,faculte,promotion\nTEST1001,ILUNGA,KALALA,Amina,Médecine,G2\nTEST1001,DOUBLON,X,B,Médecine,G2\n,SANS,MAT,C,Médecine,G2\n";

        $this->actingAs($admin)
            ->post(route('admin.voters.import'), [
                'file' => UploadedFile::fake()->createWithContent('electeurs.csv', $csv),
            ])
            ->assertRedirect();

        $report = session('import');

        $this->assertSame(3, $report['total']);
        $this->assertSame(1, $report['imported']);
        $this->assertSame(1, $report['duplicates']);
        $this->assertSame(1, $report['errors']);
        $this->assertDatabaseHas('voters', ['matricule' => 'TEST1001']);
    }

    public function test_withdrawn_candidate_cannot_be_selected(): void
    {
        [$election, $choices] = $this->ballotWorld();
        $candidate = Candidate::query()->findOrFail(array_values($choices)[0]);
        $candidate->update(['status' => CandidateStatus::Withdrawn]);
        $this->expectException(VotingException::class);

        app(VotingService::class)->cast($election, $choices);
    }

    /**
     * @return array{0: Election, 1: array<int, int>}
     */
    private function ballotWorld(): array
    {
        $election = Election::factory()->create();

        $president = Position::factory()->create([
            'election_id' => $election->id,
            'name' => 'Poste TEST A',
            'display_order' => 1,
        ]);
        $secretary = Position::factory()->create([
            'election_id' => $election->id,
            'name' => 'Poste TEST B',
            'display_order' => 2,
        ]);

        $alice = Candidate::factory()->create([
            'election_id' => $election->id,
            'position_id' => $president->id,
            'first_name' => 'Alice',
            'last_name' => 'MUKENDI',
        ]);
        $bernard = Candidate::factory()->create([
            'election_id' => $election->id,
            'position_id' => $secretary->id,
            'first_name' => 'Bernard',
            'last_name' => 'KASONGO',
        ]);

        return [$election, [
            $president->id => $alice->id,
            $secretary->id => $bernard->id,
        ]];
    }
}
