<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KraepelinQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'column_count',
        'rows_per_column',
        'time_per_column_seconds',
        'columns_data',
        'is_active',
    ];

    protected $casts = [
        'columns_data' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Generate random Kraepelin columns
     */
    public static function generateRandomColumns(int $columnCount = 50, int $rowsPerColumn = 60): array
    {
        $columns = [];
        for ($col = 0; $col < $columnCount; $col++) {
            $numbers = [];
            for ($row = 0; $row < $rowsPerColumn; $row++) {
                $numbers[] = rand(1, 9); // Single digit numbers 1-9
            }
            $columns[] = $numbers;
        }
        return $columns;
    }

    /**
     * Calculate correct answers (sum of adjacent pairs)
     */
    public function getCorrectAnswers(): array
    {
        $answers = [];
        $columns = $this->columns_data;
        
        foreach ($columns as $colIndex => $numbers) {
            $columnAnswers = [];
            for ($i = 0; $i < count($numbers) - 1; $i++) {
                $sum = $numbers[$i] + $numbers[$i + 1];
                $columnAnswers[] = $sum % 10; // Only last digit if >= 10
            }
            $answers[$colIndex] = $columnAnswers;
        }
        
        return $answers;
    }

    public function sessions()
    {
        return $this->hasMany(PsikotestSession::class, 'question_set_id')
            ->where('test_type', 'kraepelin');
    }
}
