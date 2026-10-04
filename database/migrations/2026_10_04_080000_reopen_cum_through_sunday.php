<?php

use App\Enums\ElectionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('elections')->where('slug', 'cum-2026')->update([
            'end_at' => '2026-10-04 23:59:59',
            'status' => ElectionStatus::Open->value,
            'closed_at' => null,
            'closed_by' => null,
        ]);
    }

    public function down(): void
    {
        DB::table('elections')->where('slug', 'cum-2026')->update([
            'end_at' => '2026-10-03 23:59:59',
            'status' => ElectionStatus::Closed->value,
        ]);
    }
};
