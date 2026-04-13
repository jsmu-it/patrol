<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsikotestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'raw_answers',
        'scores',
        'interpretation',
        'total_correct',
        'total_wrong',
        'accuracy_percentage',
        'speed_score',
        'consistency_score',
        'endurance_score',
    ];

    protected $casts = [
        'raw_answers' => 'array',
        'scores' => 'array',
        'interpretation' => 'array',
        'accuracy_percentage' => 'decimal:2',
        'speed_score' => 'decimal:2',
        'consistency_score' => 'decimal:2',
        'endurance_score' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(PsikotestSession::class, 'session_id');
    }

    /**
     * Calculate Kraepelin scores from raw answers
     */
    public static function calculateKraepelinScores(array $answers, KraepelinQuestion $questionSet): array
    {
        $correctAnswers = $questionSet->getCorrectAnswers();
        $columnScores = [];
        $totalCorrect = 0;
        $totalWrong = 0;
        $totalAnswered = 0;

        foreach ($answers as $colIndex => $userAnswers) {
            $correct = 0;
            $wrong = 0;
            
            if (isset($correctAnswers[$colIndex])) {
                foreach ($userAnswers as $rowIndex => $answer) {
                    $totalAnswered++;
                    if (isset($correctAnswers[$colIndex][$rowIndex]) && 
                        (int)$answer === $correctAnswers[$colIndex][$rowIndex]) {
                        $correct++;
                        $totalCorrect++;
                    } else {
                        $wrong++;
                        $totalWrong++;
                    }
                }
            }
            
            $columnScores[$colIndex] = [
                'answered' => count($userAnswers),
                'correct' => $correct,
                'wrong' => $wrong,
            ];
        }

        // Calculate metrics
        $answeredCounts = array_column($columnScores, 'answered');
        $avgSpeed = count($answeredCounts) > 0 ? array_sum($answeredCounts) / count($answeredCounts) : 0;
        
        // Consistency (lower standard deviation = more consistent)
        $stdDev = self::calculateStdDev($answeredCounts);
        $consistencyScore = max(0, 100 - ($stdDev * 10)); // Convert to 0-100 scale
        
        // Endurance (compare first half vs second half performance)
        $halfPoint = (int)(count($answeredCounts) / 2);
        $firstHalf = array_slice($answeredCounts, 0, $halfPoint);
        $secondHalf = array_slice($answeredCounts, $halfPoint);
        $firstHalfAvg = count($firstHalf) > 0 ? array_sum($firstHalf) / count($firstHalf) : 0;
        $secondHalfAvg = count($secondHalf) > 0 ? array_sum($secondHalf) / count($secondHalf) : 0;
        $enduranceScore = $firstHalfAvg > 0 ? ($secondHalfAvg / $firstHalfAvg) * 100 : 100;
        $enduranceScore = min(150, max(50, $enduranceScore)); // Cap between 50-150

        $accuracy = $totalAnswered > 0 ? ($totalCorrect / $totalAnswered) * 100 : 0;

        return [
            'column_scores' => $columnScores,
            'total_correct' => $totalCorrect,
            'total_wrong' => $totalWrong,
            'total_answered' => $totalAnswered,
            'accuracy_percentage' => round($accuracy, 2),
            'speed_score' => round($avgSpeed, 2),
            'consistency_score' => round($consistencyScore, 2),
            'endurance_score' => round($enduranceScore, 2),
        ];
    }

    /**
     * Calculate Papikostik scores from raw answers
     */
    public static function calculatePapikostikScores(array $answers, PapikostikQuestion $questionSet): array
    {
        $pairs = $questionSet->pairs;
        $dimensionScores = [];

        // Initialize all dimensions with 0
        foreach (PapikostikQuestion::DIMENSIONS as $code => $info) {
            $dimensionScores[$code] = 0;
        }

        // Count dimension selections
        foreach ($answers as $pairIndex => $selectedOption) {
            if (isset($pairs[$pairIndex])) {
                $pair = $pairs[$pairIndex];
                $dimension = ($selectedOption === 'a') ? ($pair['dimension_a'] ?? null) : ($pair['dimension_b'] ?? null);
                
                if ($dimension && isset($dimensionScores[$dimension])) {
                    $dimensionScores[$dimension]++;
                }
            }
        }

        // Generate interpretation
        $interpretation = [];
        foreach ($dimensionScores as $code => $score) {
            $dimInfo = PapikostikQuestion::DIMENSIONS[$code];
            $level = 'Medium';
            if ($score <= 3) $level = 'Low';
            elseif ($score >= 7) $level = 'High';
            
            $interpretation[$code] = [
                'name' => $dimInfo['name'],
                'category' => $dimInfo['category'],
                'score' => $score,
                'level' => $level,
            ];
        }

        return [
            'dimension_scores' => $dimensionScores,
            'interpretation' => $interpretation,
        ];
    }

    private static function calculateStdDev(array $values): float
    {
        if (count($values) === 0) return 0;
        
        $mean = array_sum($values) / count($values);
        $squaredDiffs = array_map(fn($v) => pow($v - $mean, 2), $values);
        $variance = array_sum($squaredDiffs) / count($values);
        
        return sqrt($variance);
    }

    /**
     * Get interpretation label for Kraepelin metrics
     */
    public function getKraepelinInterpretation(): array
    {
        return [
            'speed' => $this->getSpeedLabel(),
            'accuracy' => $this->getAccuracyLabel(),
            'consistency' => $this->getConsistencyLabel(),
            'endurance' => $this->getEnduranceLabel(),
        ];
    }

    private function getSpeedLabel(): string
    {
        if ($this->speed_score >= 40) return 'Sangat Cepat';
        if ($this->speed_score >= 30) return 'Cepat';
        if ($this->speed_score >= 20) return 'Cukup';
        if ($this->speed_score >= 10) return 'Lambat';
        return 'Sangat Lambat';
    }

    private function getAccuracyLabel(): string
    {
        if ($this->accuracy_percentage >= 95) return 'Sangat Teliti';
        if ($this->accuracy_percentage >= 85) return 'Teliti';
        if ($this->accuracy_percentage >= 70) return 'Cukup Teliti';
        if ($this->accuracy_percentage >= 50) return 'Kurang Teliti';
        return 'Tidak Teliti';
    }

    private function getConsistencyLabel(): string
    {
        if ($this->consistency_score >= 80) return 'Sangat Konsisten';
        if ($this->consistency_score >= 60) return 'Konsisten';
        if ($this->consistency_score >= 40) return 'Cukup Konsisten';
        if ($this->consistency_score >= 20) return 'Kurang Konsisten';
        return 'Tidak Konsisten';
    }

    private function getEnduranceLabel(): string
    {
        if ($this->endurance_score >= 110) return 'Sangat Baik (Meningkat)';
        if ($this->endurance_score >= 95) return 'Baik (Stabil)';
        if ($this->endurance_score >= 80) return 'Cukup (Sedikit Menurun)';
        if ($this->endurance_score >= 60) return 'Kurang (Menurun)';
        return 'Buruk (Sangat Menurun)';
    }
}
