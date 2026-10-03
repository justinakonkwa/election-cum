<?php

namespace App\Services;

use App\Enums\VoterStatus;
use App\Models\Election;
use App\Models\Voter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImportVoterService
{
    /**
     * @return array{total: int, imported: int, duplicates: int, errors: int, messages: list<string>}
     */
    public function import(Election $election, UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return $this->emptyReport(['Le fichier ne peut pas être lu.']);
        }

        $first = fgetcsv($handle, 0, $this->delimiter($file));

        if ($first === false) {
            fclose($handle);

            return $this->emptyReport(['Le fichier est vide.']);
        }

        $first[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($first[0] ?? '')) ?? '';
        $delimiter = $this->delimiter($file);
        rewind($handle);
        $header = fgetcsv($handle, 0, $delimiter);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($header[0] ?? '')) ?? '';

        $mapped = $this->mapHeader($header);
        $hasHeader = $mapped !== null;
        $rows = [];

        if (! $hasHeader) {
            $rows[] = $header;
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $rows[] = $row;
        }

        fclose($handle);

        $report = [
            'total' => count($rows),
            'imported' => 0,
            'duplicates' => 0,
            'errors' => 0,
            'messages' => [],
        ];

        $seen = [];

        foreach ($rows as $index => $row) {
            $line = $index + ($hasHeader ? 2 : 1);
            $data = $hasHeader ? $this->combine($mapped, $row) : $this->combineByOrder($row);
            $matricule = Voter::normalizeMatricule((string) ($data['matricule'] ?? ''));

            if ($matricule === '' || ($data['last_name'] ?? '') === '' || ($data['first_name'] ?? '') === '') {
                $report['errors']++;
                $this->pushMessage($report, "Ligne {$line} : matricule, nom et prénom sont obligatoires.");

                continue;
            }

            if (isset($seen[$matricule])) {
                $report['duplicates']++;
                $this->pushMessage($report, "Ligne {$line} : matricule {$matricule} en double dans le fichier.");

                continue;
            }

            $seen[$matricule] = true;

            $exists = Voter::query()
                ->where('election_id', $election->id)
                ->where('matricule', $matricule)
                ->exists();

            if ($exists) {
                $report['duplicates']++;

                continue;
            }

            DB::transaction(function () use ($election, $data, $matricule) {
                Voter::query()->create([
                    'election_id' => $election->id,
                    'matricule' => $matricule,
                    'last_name' => trim((string) $data['last_name']),
                    'post_name' => $this->nullable($data['post_name'] ?? null),
                    'first_name' => trim((string) $data['first_name']),
                    'faculty' => $this->nullable($data['faculty'] ?? null),
                    'promotion' => $this->nullable($data['promotion'] ?? null),
                    'phone' => $this->nullable($data['phone'] ?? null),
                    'status' => VoterStatus::Eligible,
                ]);
            });

            $report['imported']++;
        }

        return $report;
    }

    private function delimiter(UploadedFile $file): string
    {
        $sample = (string) file_get_contents($file->getRealPath(), false, null, 0, 2000);

        return substr_count($sample, ';') > substr_count($sample, ',') ? ';' : ',';
    }

    /**
     * @param  list<string|null>  $header
     * @return array<string, int>|null
     */
    private function mapHeader(array $header): ?array
    {
        $aliases = [
            'matricule' => 'matricule',
            'nom' => 'last_name',
            'postnom' => 'post_name',
            'prenom' => 'first_name',
            'prénom' => 'first_name',
            'faculte' => 'faculty',
            'faculté' => 'faculty',
            'promotion' => 'promotion',
            'telephone' => 'phone',
            'téléphone' => 'phone',
        ];

        $map = [];

        foreach ($header as $index => $column) {
            $key = mb_strtolower(trim((string) $column), 'UTF-8');

            if (isset($aliases[$key])) {
                $map[$aliases[$key]] = $index;
            }
        }

        if (! isset($map['matricule'], $map['last_name'], $map['first_name'])) {
            return null;
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $map
     * @param  list<string|null>  $row
     * @return array<string, string>
     */
    private function combine(array $map, array $row): array
    {
        $data = [];

        foreach ($map as $field => $index) {
            $data[$field] = trim((string) ($row[$index] ?? ''));
        }

        return $data;
    }

    /**
     * @param  list<string|null>  $row
     * @return array<string, string>
     */
    private function combineByOrder(array $row): array
    {
        $fields = ['matricule', 'last_name', 'post_name', 'first_name', 'faculty', 'promotion', 'phone'];
        $data = [];

        foreach ($fields as $index => $field) {
            $data[$field] = trim((string) ($row[$index] ?? ''));
        }

        return $data;
    }

    /**
     * @param  list<string|null>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        return collect($row)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array{total: int, imported: int, duplicates: int, errors: int, messages: list<string>}  $report
     */
    private function pushMessage(array &$report, string $message): void
    {
        if (count($report['messages']) < 12) {
            $report['messages'][] = $message;
        }
    }

    /**
     * @param  list<string>  $messages
     * @return array{total: int, imported: int, duplicates: int, errors: int, messages: list<string>}
     */
    private function emptyReport(array $messages): array
    {
        return [
            'total' => 0,
            'imported' => 0,
            'duplicates' => 0,
            'errors' => count($messages),
            'messages' => $messages,
        ];
    }
}
