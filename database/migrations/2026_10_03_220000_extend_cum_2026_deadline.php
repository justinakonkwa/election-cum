<?php

use App\Models\Election;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        $election = Election::query()->where('slug', 'cum-2026')->first();

        if (! $election) {
            return;
        }

        $deadline = Carbon::parse('2026-10-04 23:59:59', 'Africa/Lubumbashi');

        if ($election->end_at->lt($deadline)) {
            $election->end_at = $deadline;
            $election->save();
        }
    }

    public function down(): void
    {
        $election = Election::query()->where('slug', 'cum-2026')->first();

        if (! $election) {
            return;
        }

        $election->end_at = Carbon::parse('2026-10-03 23:59:59', 'Africa/Lubumbashi');
        $election->save();
    }
};
