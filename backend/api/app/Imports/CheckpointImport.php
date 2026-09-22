<?php

namespace App\Imports;

use App\Models\Checkpoint;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CheckpointImport implements ToCollection, WithHeadingRow
{
    /** Baris yang dilewati karena project-nya di luar hak akses pengimpor. */
    public int $dilewatiTanpaHak = 0;

    /**
     * @param int[]|null $projectYangBoleh null berarti tanpa batas (superadmin).
     *        Tanpa ini, admin satu project bisa menyelipkan titik patroli — atau
     *        menimpa titik yang sudah ada — milik project lain lewat berkas Excel.
     */
    public function __construct(private readonly ?array $projectYangBoleh = null)
    {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            // Skip rows without project name or title
            if (empty($row['project_name']) || empty($row['title'])) {
                continue;
            }

            // Find project by name
            $project = Project::where('name', $row['project_name'])->first();

            // Skip if project not found
            if (!$project) {
                continue;
            }

            if ($this->projectYangBoleh !== null && !in_array((int) $project->id, $this->projectYangBoleh, true)) {
                $this->dilewatiTanpaHak++;
                continue;
            }

            // Logic to check if checkpoint already exists for this project and title
            $existing = Checkpoint::where('project_id', $project->id)
                ->where('title', $row['title'])
                ->first();

            $data = [
                'project_id' => $project->id,
                'title' => $row['title'],
                'post_name' => $row['post_name'] ?? 'Pos Utama',
                'description' => $row['description'] ?? null,
                'latitude' => $row['latitude'] ?? null,
                'longitude' => $row['longitude'] ?? null,
                'radius_meters' => $row['radius_meters'] ?? 50,
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                $checkpoint = new Checkpoint($data);
                $checkpoint->code = $this->generateCode($project->id);
                $checkpoint->save();
            }
        }
    }

    private function generateCode(int $projectId): string
    {
        do {
            $code = 'CP-'.$projectId.'-'.strtoupper(Str::random(6));
        } while (Checkpoint::where('code', $code)->exists());

        return $code;
    }
}
