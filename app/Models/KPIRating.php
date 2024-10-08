<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\KpiRatingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thiagoprz\EloquentCompositeKey\HasCompositePrimaryKey;


class KPIRating extends Model
{
    use HasFactory;

    protected $table = 'kpis';
    protected $fillable = [
        'user_id', 'year', 'month',
        'activeness_Q1_realization', 'activeness_Q2_realization', 'activeness_Q3_realization',
        'ability_Q1_realization', 'communication_Q1_realization', 'communication_Q2_realization',
        'discipline_Q1_realization', 'discipline_Q2_realization', 'discipline_Q3_realization',
        'total_aspects', 'value_conversion',
    ];

    protected $primaryKey = ['user_id', 'year', 'month'];
    public $incrementing = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
 * @return Factory
 */
    protected static function newFactory(): Factory
    {
        return KpiRatingFactory::new();
    }

    protected $appends = ['scores', 'total_aspects', 'value_conversion'];

    private $weights = [
        'activeness_Q1' => 10, 'activeness_Q2' => 5, 'activeness_Q3' => 5,
        'ability_Q1' => 25,
        'communication_Q1' => 10, 'communication_Q2' => 10,
        'discipline_Q1' => 15, 'discipline_Q2' => 15, 'discipline_Q3' => 5
    ];

    private $targets = [
        'activeness_Q1' => 5, 'activeness_Q2' => 1, 'activeness_Q3' => 2,
        'ability_Q1' => 100,
        'communication_Q1' => 4, 'communication_Q2' => 4,
        'discipline_Q1' => 100, 'discipline_Q2' => 100, 'discipline_Q3' => 100
    ];

    // Perhitungan skor untuk setiap KPI
    public function getScoresAttribute(): array
    {
        $scores = [];
        foreach ($this->weights as $key => $weight) {
            $realization = $this->{$key . '_realization'};
            $target = $this->targets[$key];
            $score = $realization ? ($realization / $target) * 100 : 0;
            $finalScore = ($score * $weight) / 100;

            $kpi = explode('_', $key)[1];

            $scores[$key] = [
                'aspect' => explode('_', $key)[0],
                'kpi' => $kpi,
                'realization' => $realization,
                'score' => $score,
                'final_score' => $finalScore
            ];
        }
        return $scores;
    }

    public function getTotalAspectsAttribute(): float|int
    {
        return array_sum(array_column($this->scores, 'final_score'));
    }

    public function getAspectValueConversion($aspect, $total): string
    {
        $ranges = [
            'activeness' => [
                'A' => [17, 20], 'B' => [13, 16], 'C' => [9, 12], 'D' => [5, 8], 'E' => [1, 4]
            ],
            'ability' => [
                'A' => [21, 25], 'B' => [16, 20], 'C' => [11, 15], 'D' => [6, 10], 'E' => [1, 5]
            ],
            'communication' => [
                'A' => [17, 20], 'B' => [13, 16], 'C' => [9, 12], 'D' => [5, 8], 'E' => [1, 4]
            ],
            'discipline' => [
                'A' => [29, 35], 'B' => [22, 28], 'C' => [15, 21], 'D' => [8, 14], 'E' => [1, 7]
            ]
        ];

        foreach ($ranges[$aspect] as $grade => $range) {
            if ($total >= $range[0] && $total <= $range[1]) {
                return $grade;
            }
        }
        return 'E';
    }

    public function getValueConversion($total): string
    {
        $result = '';
        if ($total >= 96) {
            $result = 'Excellent';
        } elseif ($total >= 86) {
            $result = 'Very Good';
        } elseif ($total >= 76) {
            $result = 'Good';
        } elseif ($total >= 66) {
            $result = 'Average';
        } elseif ($total >= 56) {
            $result = 'Fair';
        } elseif ($total >= 36) {
            $result = 'Poor';
        } else {
            $result = 'Very Poor';
        }
        return $result;
    }
}
