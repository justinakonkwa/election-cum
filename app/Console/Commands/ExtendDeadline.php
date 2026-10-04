<?php

namespace App\Console\Commands;

use App\Enums\ElectionStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExtendDeadline extends Command
{
    protected $signature = 'cum:extend-deadline';

    protected $description = 'Keep the CUM vote open through 4 October 2026 at 23:59 Lubumbashi time.';

    public function handle(): int
    {
        $deadline = '2026-10-04 23:59:59';
        $connection = DB::getDefaultConnection();
        $host = config('database.connections.'.$connection.'.host');

        $before = DB::table('elections')->orderBy('id')->get(['id', 'slug', 'end_at', 'status']);
        $this->line('deadline-before driver='.DB::getDriverName().' host='.$host.' rows='.$before->map(
            fn ($row) => $row->id.':'.$row->slug.':'.$row->end_at.':'.$row->status
        )->implode(','));

        if (now('Africa/Lubumbashi')->gt($deadline)) {
            $this->line('deadline-skipped');

            return self::SUCCESS;
        }

        $updated = DB::table('elections')->update([
            'end_at' => $deadline,
            'status' => ElectionStatus::Open->value,
            'closed_at' => null,
            'closed_by' => null,
        ]);

        $this->line('deadline-updated count='.$updated);

        return self::SUCCESS;
    }
}
