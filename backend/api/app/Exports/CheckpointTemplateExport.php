<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CheckpointTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        // Template without data, just headers.
        return new Collection();
    }

    public function headings(): array
    {
        return [
            'project_name',
            'title',
            'post_name',
            'description',
            'latitude',
            'longitude',
            'radius_meters',
        ];
    }
}
