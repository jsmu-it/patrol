<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PapikostikQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pairs',
        'is_active',
    ];

    protected $casts = [
        'pairs' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * 20 PAPIKOSTIK Dimensions
     */
    public const DIMENSIONS = [
        'G' => ['name' => 'Hard Intense Worker', 'category' => 'Work Direction'],
        'L' => ['name' => 'Leadership Role', 'category' => 'Leadership'],
        'I' => ['name' => 'Ease in Decision Making', 'category' => 'Leadership'],
        'T' => ['name' => 'Pace', 'category' => 'Activity'],
        'V' => ['name' => 'Vigorous Type', 'category' => 'Activity'],
        'S' => ['name' => 'Social Extension', 'category' => 'Social Nature'],
        'R' => ['name' => 'Theoretical Type', 'category' => 'Work Style'],
        'D' => ['name' => 'Interest in Working with Details', 'category' => 'Work Style'],
        'C' => ['name' => 'Organized Type', 'category' => 'Work Style'],
        'E' => ['name' => 'Emotional Restraint', 'category' => 'Temperament'],
        'N' => ['name' => 'Need to Finish Task', 'category' => 'Work Direction'],
        'A' => ['name' => 'Need for Achievement', 'category' => 'Work Direction'],
        'P' => ['name' => 'Need to Control Others', 'category' => 'Leadership'],
        'X' => ['name' => 'Need to be Noticed', 'category' => 'Social Nature'],
        'B' => ['name' => 'Need to Belong to Groups', 'category' => 'Social Nature'],
        'O' => ['name' => 'Need for Closeness', 'category' => 'Social Nature'],
        'Z' => ['name' => 'Need for Change', 'category' => 'Work Direction'],
        'K' => ['name' => 'Need to be Forceful', 'category' => 'Temperament'],
        'F' => ['name' => 'Need to Support Authority', 'category' => 'Followership'],
        'W' => ['name' => 'Need for Rules and Supervision', 'category' => 'Followership'],
    ];

    /**
     * Get dimension info
     */
    public static function getDimension(string $code): ?array
    {
        return self::DIMENSIONS[$code] ?? null;
    }

    /**
     * Get all dimensions grouped by category
     */
    public static function getDimensionsByCategory(): array
    {
        $grouped = [];
        foreach (self::DIMENSIONS as $code => $info) {
            $category = $info['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][$code] = $info['name'];
        }
        return $grouped;
    }

    public function sessions()
    {
        return $this->hasMany(PsikotestSession::class, 'question_set_id')
            ->where('test_type', 'papikostik');
    }
}
